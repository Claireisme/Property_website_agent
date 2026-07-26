<?php

use App\Http\Middleware\SetLocale;
use App\Models\Agency;
use App\Models\BuyerAccessRequest;
use App\Models\Enquiry;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Property;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Services\BuyerEmailVerificationService;
use App\Services\EmailNotificationService;
use App\Support\BidIncrementRules;
use App\Support\Locales;
use App\Support\LocationOptions;
use App\Support\PropertyOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

$siteRoutes = function (): void {
    Route::get('/', function () {
        $agency = Agency::query()->first();
        $properties = Property::query()
            ->with(['images', 'translations'])
            ->whereIn('status', ['available', 'under_offer', 'sale_agreed'])
            ->newestFirst()
            ->take(6)
            ->get();

        return view('home', [
            'agency' => $agency,
            'properties' => $properties,
        ]);
    })->name('home');

    Route::get('/properties', function (Request $request) {
        $category = $request->string('category')->toString();
        $category = array_key_exists($category, PropertyOptions::listingCategories()) ? $category : 'all';
        $sort = $request->string('sort', 'newest')->toString();
        $sort = array_key_exists($sort, PropertyOptions::sortOptions()) ? $sort : 'newest';
        $status = $request->string('status')->toString();
        $status = array_key_exists($status, PropertyOptions::publicStatuses()) ? $status : null;
        $region = $request->string('region')->toString();

        $properties = Property::query()
            ->with(['images', 'translations'])
            ->whereIn('status', array_keys(PropertyOptions::publicStatuses()))
            ->listingCategory($category)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where(function ($query) use ($request): void {
                    $value = '%'.$request->string('q')->toString().'%';
                    $query->where('title', 'like', $value)
                        ->orWhere('town', 'like', $value)
                        ->orWhere('county', 'like', $value)
                        ->orWhere('address_line_1', 'like', $value);
                });
            })
            ->when($request->filled('region'), function ($query) use ($region): void {
                $counties = LocationOptions::countiesForRegion($region);
                $towns = LocationOptions::townsForRegion($region);

                if ($towns !== []) {
                    $query->whereIn('town', $towns);

                    if ($counties !== []) {
                        $query->whereIn('county', $counties);
                    }

                    return;
                }

                if ($counties !== []) {
                    $query->whereIn('county', $counties);
                }
            })
            ->when($request->filled('property_type'), fn ($query) => $query->where('property_type', $request->string('property_type')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->integer('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->integer('max_price')))
            ->when($request->filled('min_beds'), fn ($query) => $query->where('bedrooms', '>=', $request->integer('min_beds')))
            ->when($request->filled('min_baths'), fn ($query) => $query->where('bathrooms', '>=', $request->integer('min_baths')))
            ->when($request->filled('min_area'), fn ($query) => $query->where('floor_area_m2', '>=', $request->integer('min_area')))
            ->when($request->filled('max_area'), fn ($query) => $query->where('floor_area_m2', '<=', $request->integer('max_area')))
            ->when($request->filled('min_ber'), function ($query) use ($request): void {
                $ratings = PropertyOptions::berRatingsAtLeast($request->string('min_ber')->toString());

                if ($ratings !== []) {
                    $query->whereIn('ber_rating', $ratings);
                }
            })
            ->when($request->filled('facilities'), function ($query) use ($request): void {
                foreach ((array) $request->input('facilities', []) as $facility) {
                    $values = PropertyOptions::facilityFilterValues((string) $facility);

                    if ($values !== []) {
                        $query->where(function ($query) use ($values): void {
                            foreach ($values as $index => $value) {
                                $method = $index === 0 ? 'whereJsonContains' : 'orWhereJsonContains';

                                $query->{$method}('facilities', $value);
                            }
                        });
                    }
                }
            })
            ->when(
                $sort === 'price_asc',
                fn ($query) => $query->orderByRaw('price is null')->orderBy('price'),
                fn ($query) => $query
                    ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
                    ->when($sort === 'beds_desc', fn ($query) => $query->orderByDesc('bedrooms'))
                    ->when($sort === 'area_desc', fn ($query) => $query->orderByDesc('floor_area_m2'))
                    ->when($sort === 'newest', fn ($query) => $query->newestFirst()),
            )
            ->get();

        return view('properties.index', [
            'category' => $category,
            'sort' => $sort,
            'status' => $status,
            'properties' => $properties,
        ]);
    })->name('properties.index');

    Route::get('/properties/{property:slug}', function (Property $property) {
        abort_unless(array_key_exists($property->status, PropertyOptions::publicStatuses()), 404);

        $property->load(['agency', 'images', 'teamMember', 'translations']);
        $buyerUser = auth()->user()?->isBuyer() ? auth()->user() : null;
        $buyerAccessRequest = $buyerUser
            ? $property->buyerAccessRequests()
                ->where(function ($query) use ($buyerUser): void {
                    $query->where('user_id', $buyerUser->id)
                        ->orWhere('buyer_email', $buyerUser->email);
                })
                ->latest('requested_at')
                ->latest('id')
                ->first()
            : null;

        return view('properties.show', [
            'buyerAccessRequest' => $buyerAccessRequest,
            'buyerUser' => $buyerUser,
            'bidIncrementAmount' => BidIncrementRules::incrementForProperty($property),
            'currentOfferBaseAmount' => BidIncrementRules::currentBaseAmount($property),
            'nextOfferAmount' => BidIncrementRules::nextOfferAmount($property),
            'property' => $property,
        ]);
    })->name('properties.show');

    Route::get('/about', function () {
        return view('about', [
            'agency' => Agency::query()->first(),
        ]);
    })->name('about');

    Route::post('/buyer/login', function (Request $request) {
        $credentials = $request->validate([
            'buyer_login_email' => ['required', 'email', 'max:255'],
            'buyer_login_password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['buyer_login_email'],
            'password' => $credentials['buyer_login_password'],
            'role' => 'buyer',
        ])) {
            return back()->withErrors([
                'buyer_login_email' => 'These buyer account details do not match our records.',
            ])->onlyInput('buyer_login_email');
        }

        $request->session()->regenerate();

        return back()->with('offer_status', 'You are signed in to your buyer account.');
    })->name('buyer.login');

    Route::post('/buyer/password-reset-code', function (Request $request, BuyerEmailVerificationService $verification) {
        $data = $request->validate([
            'buyer_reset_email' => ['required', 'email', 'max:255'],
        ]);

        $buyer = User::query()
            ->where('email', $data['buyer_reset_email'])
            ->where('role', 'buyer')
            ->first();

        if ($buyer) {
            $verification->issuePasswordReset($buyer->email);
        }

        return back()
            ->withInput($request->only('buyer_reset_email'))
            ->with('offer_status', 'If a buyer account exists for that email, a password reset code has been sent.');
    })->name('buyer.password-reset-code.store');

    Route::post('/buyer/password-reset', function (Request $request, BuyerEmailVerificationService $verification) {
        $data = $request->validate([
            'buyer_reset_email' => ['required', 'email', 'max:255'],
            'buyer_reset_code' => ['required', 'string', 'size:6'],
            'buyer_reset_password' => ['required', 'string', Password::min(7), 'confirmed'],
        ]);

        $buyer = User::query()
            ->where('email', $data['buyer_reset_email'])
            ->where('role', 'buyer')
            ->first();

        if (! $buyer || ! $verification->verify($buyer->email, $data['buyer_reset_code'])) {
            throw ValidationException::withMessages([
                'buyer_reset_code' => 'The reset code is invalid or has expired.',
            ]);
        }

        $buyer->forceFill([
            'password' => $data['buyer_reset_password'],
            'email_verified_at' => $buyer->email_verified_at ?: now(),
        ])->save();

        Auth::login($buyer);
        $request->session()->regenerate();

        return back()->with('offer_status', 'Your password has been reset and you are signed in.');
    })->name('buyer.password-reset');

    Route::post('/buyer/email-verification-code', function (Request $request, BuyerEmailVerificationService $verification) {
        $data = $request->validate([
            'buyer_register_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $verification->issue($data['buyer_register_email']);

        return back()
            ->withInput($request->only('buyer_register_email'))
            ->with('offer_toast', 'A verification code has been sent to your email address.')
            ->with('offer_code_cooldown_seconds', 60);
    })->name('buyer.verification-code.store');

    Route::post('/buyer/register', function (Request $request, BuyerEmailVerificationService $verification) {
        $data = $request->validate([
            'buyer_register_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'buyer_register_password' => ['required', 'string', Password::min(7)],
            'buyer_register_verification_code' => ['required', 'string', 'size:6'],
        ]);

        $email = $data['buyer_register_email'];

        if (! $verification->verify($email, $data['buyer_register_verification_code'])) {
            throw ValidationException::withMessages([
                'buyer_register_verification_code' => 'The verification code is invalid or has expired.',
            ]);
        }

        $name = str($email)->before('@')->replace(['.', '_', '-'], ' ')->headline()->toString();

        $buyer = User::query()->create([
            'name' => $name ?: $email,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => $data['buyer_register_password'],
            'role' => 'buyer',
        ]);

        Auth::login($buyer);
        $request->session()->regenerate();

        return back()->with('offer_status', 'Buyer account created. You can now submit verification documents for this property.');
    })->name('buyer.register');

    Route::post('/buyer/logout', function (Request $request) {
        if (auth()->user()?->isBuyer()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return back()->with('offer_status', 'You are signed out of your buyer account.');
    })->name('buyer.logout');

    Route::post('/properties/{property:slug}/enquiries', function (Request $request, Property $property) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'enquiry_type' => ['nullable', 'string', Rule::in(array_keys(PropertyOptions::enquiryTypes()))],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);
        $data['enquiry_type'] ??= 'question';

        $enquiry = Enquiry::query()->create($data + [
            'property_id' => $property->id,
            'source' => 'agency_site',
            'status' => 'new',
        ]);

        app(EmailNotificationService::class)->sendEnquiryReceived($enquiry->load('property.agency', 'property.teamMember'));

        $redirectUrl = preg_replace('/#.*$/', '', url()->previous()).'#property-enquiry';

        return redirect()->to($redirectUrl)->with('status', __('site.messages.enquiry_sent'));
    })->name('properties.enquiries.store');

    Route::post('/properties/{property:slug}/offers', function (Request $request, Property $property) {
        abort_unless($property->online_offers_enabled, 404);

        $buyer = auth()->user();
        abort_unless($buyer?->isBuyer(), 403);

        $data = $request->validate([
            'buyer_access_request_id' => ['required', 'integer', 'exists:buyer_access_requests,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'buyer_position' => ['nullable', 'string', 'max:255'],
            'financing_type' => ['nullable', 'string', 'max:255'],
            'mortgage_approval_status' => ['nullable', 'string', 'max:255'],
            'current_property_status' => ['nullable', 'string', 'max:255'],
            'conditions' => ['nullable', 'array'],
            'conditions.*' => ['string', 'max:255'],
            'proof_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'message' => ['nullable', 'string', 'max:5000'],
            'consent_to_terms' => ['accepted'],
        ]);

        $buyerAccessRequest = BuyerAccessRequest::query()
            ->approved()
            ->whereKey($data['buyer_access_request_id'])
            ->where('property_id', $property->id)
            ->where(function ($query) use ($buyer): void {
                $query->where('user_id', $buyer->id)
                    ->orWhere('buyer_email', $buyer->email);
            })
            ->first();

        abort_unless($buyerAccessRequest, 403);

        if ($message = BidIncrementRules::amountValidationMessage($property, (int) $data['amount'])) {
            throw ValidationException::withMessages([
                'amount' => $message,
            ]);
        }

        if ($buyerAccessRequest->user_id === null) {
            $buyerAccessRequest->forceFill(['user_id' => $buyer->id])->save();
        }

        $proofDocumentPath = $request->file('proof_document')
            ? $request->file('proof_document')->store('offer-proofs')
            : null;

        $offer = Offer::query()->create([
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_access_request_id' => $buyerAccessRequest->id,
            'buyer_name' => $buyerAccessRequest->buyer_name ?: $buyer->name,
            'buyer_email' => $buyer->email,
            'buyer_phone' => $buyerAccessRequest->buyer_phone,
            'amount' => $data['amount'],
            'status' => 'submitted',
            'buyer_position' => $data['buyer_position'] ?? null,
            'financing_type' => $data['financing_type'] ?? null,
            'mortgage_approval_status' => $data['mortgage_approval_status'] ?? null,
            'current_property_status' => $data['current_property_status'] ?? null,
            'conditions' => $data['conditions'] ?? [],
            'proof_document_path' => $proofDocumentPath,
            'message' => $data['message'] ?? null,
            'consent_to_terms' => true,
            'submitted_at' => now(),
        ]);

        OfferEvent::query()->create([
            'offer_id' => $offer->id,
            'actor_type' => 'buyer',
            'event_type' => 'offer_submitted',
            'metadata' => [
                'amount' => $offer->amount,
                'financing_type' => $offer->financing_type,
                'has_proof_document' => filled($offer->proof_document_path),
                'buyer_access_request_id' => $buyerAccessRequest->id,
            ],
        ]);

        app(EmailNotificationService::class)->sendOfferSubmitted($offer->load('property.agency', 'property.teamMember'));

        return back()->with('offer_status', __('site.messages.offer_sent'));
    })->name('properties.offers.store');

    Route::post('/properties/{property:slug}/buyer-access-requests', function (Request $request, Property $property) {
        abort_unless($property->online_offers_enabled, 404);

        $authUser = auth()->user();

        if ($authUser && ! $authUser->isBuyer()) {
            return back()->withErrors([
                'buyer_email' => 'Please use a buyer account to register bidding access.',
            ])->withInput();
        }

        $buyer = $authUser?->isBuyer() ? $authUser : null;

        if (! $buyer) {
            return back()->withErrors([
                'buyer_login_email' => 'Please create or sign in to a buyer account before requesting bidding access.',
            ])->withInput();
        }

        $rules = [
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_phone' => ['nullable', 'string', 'max:255'],
            'initial_offer_amount' => ['required', 'integer', 'min:1'],
            'buyer_position' => ['nullable', 'string', 'max:255'],
            'financing_type' => ['nullable', 'string', 'max:255'],
            'mortgage_approval_status' => ['nullable', 'string', 'max:255'],
            'current_property_status' => ['nullable', 'string', 'max:255'],
            'proof_of_funds_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'identity_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'message' => ['nullable', 'string', 'max:5000'],
            'consent_to_terms' => ['accepted'],
        ];

        $data = $request->validate($rules);

        if ($message = BidIncrementRules::amountValidationMessage($property, (int) $data['initial_offer_amount'])) {
            throw ValidationException::withMessages([
                'initial_offer_amount' => $message,
            ]);
        }

        if ($buyer->name !== $data['buyer_name']) {
            $buyer->forceFill(['name' => $data['buyer_name']])->save();
        }

        $data['buyer_email'] = $buyer->email;

        $existingRequest = BuyerAccessRequest::query()
            ->where('property_id', $property->id)
            ->whereNotIn('status', ['rejected', 'withdrawn'])
            ->where(function ($query) use ($buyer): void {
                $query->where('user_id', $buyer->id)
                    ->orWhere('buyer_email', $buyer->email);
            })
            ->latest('requested_at')
            ->latest('id')
            ->first();

        if ($existingRequest?->status === 'approved') {
            if ($existingRequest->user_id === null) {
                $existingRequest->forceFill(['user_id' => $buyer->id])->save();
            }

            return back()->with('offer_status', 'Your bidding access is already approved. You can now submit an offer.');
        }

        $proofOfFundsPath = $request->file('proof_of_funds_document')
            ? $request->file('proof_of_funds_document')->store('buyer-access/proof-of-funds')
            : $existingRequest?->proof_of_funds_path;

        $identityDocumentPath = $request->file('identity_document')
            ? $request->file('identity_document')->store('buyer-access/identity-documents')
            : $existingRequest?->identity_document_path;

        $documentsUploadedAt = ($request->hasFile('proof_of_funds_document') || $request->hasFile('identity_document'))
            ? now()
            : $existingRequest?->documents_uploaded_at;

        $payload = [
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_name' => $data['buyer_name'],
            'buyer_email' => $data['buyer_email'],
            'buyer_phone' => $data['buyer_phone'] ?? null,
            'status' => $proofOfFundsPath && $identityDocumentPath ? 'pending_review' : 'pending_documents',
            'initial_offer_amount' => $data['initial_offer_amount'],
            'buyer_position' => $data['buyer_position'] ?? null,
            'financing_type' => $data['financing_type'] ?? null,
            'mortgage_approval_status' => $data['mortgage_approval_status'] ?? null,
            'current_property_status' => $data['current_property_status'] ?? null,
            'proof_of_funds_path' => $proofOfFundsPath,
            'identity_document_path' => $identityDocumentPath,
            'message' => $data['message'] ?? null,
            'consent_to_terms' => true,
            'requested_at' => $existingRequest?->requested_at ?: now(),
            'documents_uploaded_at' => $documentsUploadedAt ?: (($proofOfFundsPath || $identityDocumentPath) ? now() : null),
        ];

        if ($existingRequest) {
            $existingRequest->update($payload);
            $buyerAccessRequest = $existingRequest->refresh();
        } else {
            $buyerAccessRequest = BuyerAccessRequest::query()->create($payload);
        }

        app(EmailNotificationService::class)->sendBuyerAccessSubmitted($buyerAccessRequest->load('property.agency', 'property.teamMember'));

        return back()->with('offer_status', __('site.messages.buyer_access_requested'));
    })->name('properties.buyer-access-requests.store');

    Route::get('/contact', function () {
        return view('contact', [
            'agency' => Agency::query()->first(),
        ]);
    })->name('contact');

    Route::post('/contact', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $enquiry = Enquiry::query()->create($data + [
            'source' => 'agency_site',
            'status' => 'new',
        ]);

        app(EmailNotificationService::class)->sendEnquiryReceived($enquiry);

        return back()->with('status', __('site.messages.contact_sent'));
    })->name('contact.store');

    Route::get('/valuation', function () {
        return view('valuation');
    })->name('valuation');

    Route::get('/mortgages', function () {
        return view('mortgages', [
            'agency' => Agency::query()->first(),
        ]);
    })->name('mortgages');

    Route::post('/valuation', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'property_address' => ['required', 'string', 'max:255'],
            'eircode' => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', 'string', 'max:255'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'preferred_contact_method' => ['nullable', 'string', 'max:255'],
            'selling_timeline' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $valuationRequest = ValuationRequest::query()->create($data + [
            'source' => 'agency_site',
            'status' => 'new',
        ]);

        app(EmailNotificationService::class)->sendValuationReceived($valuationRequest);

        return back()->with('status', __('site.messages.valuation_sent'));
    })->name('valuation.store');
};

$siteRoutes();

Route::prefix('{locale}')
    ->where(['locale' => implode('|', Locales::nonDefaultCodes())])
    ->middleware(SetLocale::class)
    ->name('localized.')
    ->group($siteRoutes);

Route::middleware('auth')
    ->prefix('admin/buyer-access-requests/{buyerAccessRequest}')
    ->name('admin.buyer-access-requests.')
    ->group(function (): void {
        Route::get('/documents/{document}', function (BuyerAccessRequest $buyerAccessRequest, string $document) {
            abort_unless(auth()->user()?->can('view', $buyerAccessRequest), 403);

            $path = match ($document) {
                'proof' => $buyerAccessRequest->proof_of_funds_path,
                'identity' => $buyerAccessRequest->identity_document_path,
                default => null,
            };

            abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

            $filename = str_replace('"', '', basename($path));
            $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };

            return response()->file(Storage::disk('local')->path($path), [
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        })->where('document', 'proof|identity')->name('documents.show');

        Route::get('/documents/{document}/download', function (BuyerAccessRequest $buyerAccessRequest, string $document) {
            abort_unless(auth()->user()?->can('view', $buyerAccessRequest), 403);

            $path = match ($document) {
                'proof' => $buyerAccessRequest->proof_of_funds_path,
                'identity' => $buyerAccessRequest->identity_document_path,
                default => null,
            };

            abort_unless(filled($path) && Storage::disk('local')->exists($path), 404);

            $mimeType = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };

            return response()->download(Storage::disk('local')->path($path), basename($path), [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
            ]);
        })->where('document', 'proof|identity')->name('documents.download');

        Route::post('/approve', function (BuyerAccessRequest $buyerAccessRequest) {
            abort_unless(auth()->user()?->can('update', $buyerAccessRequest), 403);

            $buyerAccessRequest->forceFill([
                'status' => 'approved',
                'reviewed_at' => now(),
                'approved_at' => now(),
                'rejected_at' => null,
            ])->save();

            if ($buyerAccessRequest->initial_offer_amount) {
                $offer = Offer::query()->firstOrCreate(
                    ['buyer_access_request_id' => $buyerAccessRequest->id],
                    [
                        'property_id' => $buyerAccessRequest->property_id,
                        'user_id' => $buyerAccessRequest->user_id,
                        'buyer_name' => $buyerAccessRequest->buyer_name,
                        'buyer_email' => $buyerAccessRequest->buyer_email,
                        'buyer_phone' => $buyerAccessRequest->buyer_phone,
                        'amount' => $buyerAccessRequest->initial_offer_amount,
                        'status' => 'submitted',
                        'buyer_position' => $buyerAccessRequest->buyer_position,
                        'financing_type' => $buyerAccessRequest->financing_type,
                        'mortgage_approval_status' => $buyerAccessRequest->mortgage_approval_status,
                        'current_property_status' => $buyerAccessRequest->current_property_status,
                        'conditions' => [],
                        'message' => $buyerAccessRequest->message,
                        'consent_to_terms' => true,
                        'submitted_at' => now(),
                    ],
                );

                if ($offer->wasRecentlyCreated) {
                    OfferEvent::query()->create([
                        'offer_id' => $offer->id,
                        'actor_type' => 'agent',
                        'event_type' => 'offer_created_from_access_approval',
                        'metadata' => [
                            'amount' => $offer->amount,
                            'buyer_access_request_id' => $buyerAccessRequest->id,
                        ],
                    ]);

                    app(EmailNotificationService::class)->sendOfferSubmitted($offer->load('property.agency', 'property.teamMember'));
                }
            }

            app(EmailNotificationService::class)->sendBuyerAccessApproved($buyerAccessRequest->load('property.agency', 'property.teamMember'));

            return back()->with('buyer_access_review_status', 'Buyer access approved.');
        })->name('approve');

        Route::post('/reject', function (BuyerAccessRequest $buyerAccessRequest) {
            abort_unless(auth()->user()?->can('update', $buyerAccessRequest), 403);

            $buyerAccessRequest->forceFill([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'approved_at' => null,
                'rejected_at' => now(),
            ])->save();

            app(EmailNotificationService::class)->sendBuyerAccessRejected($buyerAccessRequest->load('property.agency', 'property.teamMember'));

            return back()->with('buyer_access_review_status', 'Buyer access rejected.');
        })->name('reject');
    });
