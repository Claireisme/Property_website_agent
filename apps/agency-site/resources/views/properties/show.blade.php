@php
    $galleryImages = $property->images->map(fn ($image) => [
        'large' => $image->publicUrl($image->large_url ?: $image->detail_url ?: $image->original_url),
        'detail' => $image->publicUrl($image->detail_url ?: $image->large_url ?: $image->original_url),
        'thumb' => $image->publicUrl($image->thumbnail_url ?: $image->card_url ?: $image->original_url),
        'caption' => $image->caption ?: $property->localizedTitle(),
    ])->values();
    $location = collect([$property->town, $property->county])->filter()->join(', ');
    $category = \App\Support\PropertyOptions::listingCategories()[$property->listingCategory()] ?? 'Property';
    $addressParts = collect([
        $property->address_line_1,
        $property->address_line_2,
        $property->town,
        $property->county,
        $property->eircode,
    ])
        ->filter()
        ->unique(fn (string $part): string => strtolower(trim($part)));
    $summaryAddressParts = collect([
        $property->address_line_1,
        $property->address_line_2,
    ])
        ->filter()
        ->values();
    $summaryAddressSearch = strtolower($summaryAddressParts->join(', '));

    foreach ([$property->town, $property->county, $property->eircode] as $addressPart) {
        $addressPart = is_string($addressPart) ? trim($addressPart) : $addressPart;

        if (! $addressPart) {
            continue;
        }

        $needle = strtolower((string) $addressPart);

        if (! str_contains($summaryAddressSearch, $needle)) {
            $summaryAddressParts->push($addressPart);
            $summaryAddressSearch = strtolower($summaryAddressParts->join(', '));
        }
    }

    $summaryAddress = $summaryAddressParts
        ->unique(fn (string $part): string => strtolower(trim($part)))
        ->join(', ');
    $address = collect([$summaryAddress ?: $addressParts->join(', '), 'Ireland'])
        ->filter()
        ->join(', ');
    $mapQuery = ($property->latitude && $property->longitude)
        ? $property->latitude.','.$property->longitude
        : $address;
    $mapUrl = 'https://maps.google.com/maps?q='.rawurlencode($mapQuery).'&z=15&output=embed';
    $nearbyAmenities = \App\Support\PropertySurroundings::amenities($property);
    $berLabel = \App\Support\PropertyOptions::normalizeBerRating($property->ber_rating);
    $berAssetLevel = \App\Support\PropertyOptions::berAssetLevel($property->ber_rating);
    $berAssetUrl = $berAssetLevel ? asset("images/ber/ber-{$berAssetLevel}.png") : null;
    $statusLabel = \App\Support\PropertyOptions::statuses()[$property->status] ?? ucfirst(str_replace('_', ' ', $property->status));
    $priceLabel = \App\Support\PropertyOptions::priceQualifiers()[$property->price_qualifier] ?? 'Price';
    $priceLabel = $property->transaction_type === 'rent' ? 'Monthly rent' : $priceLabel;
    $pricePeriod = $property->transaction_type === 'rent' && $property->price ? 'per month' : null;
    $floorArea = $property->floor_area_m2 ? (float) $property->floor_area_m2 : null;
    $floorAreaLabel = $floorArea ? rtrim(rtrim(number_format($floorArea, 2), '0'), '.') : null;
    $pricePerSqm = ($property->price && $floorArea && $floorArea > 0) ? (int) round($property->price / $floorArea) : null;
    $pricePerSqmLabel = $pricePerSqm
        ? 'EUR '.number_format($pricePerSqm).' per m²'.($property->transaction_type === 'rent' ? ' / month' : '')
        : null;
    $propertyTypeLabel = $property->property_type
        ? (\App\Support\PropertyOptions::propertyTypes()[$property->property_type] ?? ucwords(str_replace('_', ' ', $property->property_type)))
        : 'Property';
    $descriptionHtml = \App\Support\PropertyDescriptionFormatter::toHtml($property->localizedDescription());
    $regionKey = \App\Support\LocationOptions::regionKeyFor($property->county, $property->town);
    $regionLabel = $regionKey ? (\App\Support\LocationOptions::regions()[$regionKey] ?? $location) : null;
    $countyRegionKey = \App\Support\LocationOptions::regionKeyFor($property->county, null);
    $priceBandMin = $property->price ? (int) floor($property->price * 0.9 / 1000) * 1000 : null;
    $priceBandMax = $property->price ? (int) ceil($property->price * 1.1 / 1000) * 1000 : null;
    $contextCategory = $property->listingCategory();
    $relatedLinks = collect([
        $regionKey && $regionLabel ? [
            'eyebrow' => 'Area search',
            'label' => 'Properties in '.$regionLabel,
            'meta' => $property->county ? $property->county.' listings' : 'Local listings',
            'theme' => 'area',
            'url' => \App\Support\LocaleUrl::route('properties.index', [
                'category' => $contextCategory,
                'region' => $regionKey,
            ]),
        ] : null,
        $priceBandMin && $priceBandMax ? [
            'eyebrow' => 'Similar budget',
            'label' => 'EUR '.number_format($priceBandMin).' - '.number_format($priceBandMax),
            'meta' => 'Within 10%'.($property->county ? ' in '.$property->county : ''),
            'theme' => 'budget',
            'url' => \App\Support\LocaleUrl::route('properties.index', array_filter([
                'category' => $contextCategory,
                'region' => $countyRegionKey,
                'min_price' => $priceBandMin,
                'max_price' => $priceBandMax,
            ], fn ($value) => filled($value))),
        ] : null,
    ])->filter()->values();
    $agencyName = $property->agency?->trading_name ?: ($property->agency?->name ?: 'the agency');
    $listingAgent = $property->teamMember?->is_active ? $property->teamMember : null;
    $enquiryContactName = $listingAgent?->name ?: $agencyName;
    $enquiryContactRole = $listingAgent?->role ?: null;
    $enquiryAvatarPath = $listingAgent?->photo_path ?: $property->agency?->logo_path;
    $enquiryAvatarUrl = $enquiryAvatarPath ? asset('storage/'.$enquiryAvatarPath) : null;
    $enquiryAvatarAlt = $listingAgent
        ? $listingAgent->name.' profile photo'
        : $agencyName.' logo';
    $enquiryInitials = collect(preg_split('/\s+/', $enquiryContactName))
        ->filter()
        ->take(2)
        ->map(fn (string $part): string => mb_substr($part, 0, 1))
        ->join('');
    $enquiryInitials = mb_strtoupper($enquiryInitials ?: 'EA');
    $enquirySent = session('status') === __('site.messages.enquiry_sent');
    $enquiryShouldOpen = ! $enquirySent && ($errors->has('name') || $errors->has('email') || $errors->has('phone') || $errors->has('enquiry_type') || $errors->has('message'));
    $enquiryTypes = \App\Support\PropertyOptions::enquiryTypes();
    $authUser = auth()->user();
    $buyerUser = $buyerUser ?? ($authUser?->isBuyer() ? $authUser : null);
    $isStaffUser = $authUser && ! $authUser->isBuyer();
    $buyerAccessRequest = $buyerAccessRequest ?? null;
    $buyerAccessStatus = $buyerAccessRequest?->status;
    $buyerAccessStatusLabel = $buyerAccessStatus
        ? (\App\Support\PropertyOptions::buyerAccessStatuses()[$buyerAccessStatus] ?? str($buyerAccessStatus)->replace('_', ' ')->title())
        : null;
    $buyerCanBid = $buyerAccessStatus === 'approved';
    $bidIncrementAmount = $bidIncrementAmount ?? \App\Support\BidIncrementRules::incrementForProperty($property);
    $currentOfferBaseAmount = $currentOfferBaseAmount ?? \App\Support\BidIncrementRules::currentBaseAmount($property);
    $nextOfferAmount = $nextOfferAmount ?? \App\Support\BidIncrementRules::nextOfferAmount($property);
    $buyerInitialOfferValue = max((int) ($buyerAccessRequest?->initial_offer_amount ?? 0), (int) $nextOfferAmount);
    $bidErrorFields = [
        'buyer_register_email',
        'buyer_register_password',
        'buyer_register_verification_code',
        'buyer_login_email',
        'buyer_login_password',
        'buyer_reset_email',
        'buyer_reset_code',
        'buyer_reset_password',
        'buyer_reset_password_confirmation',
        'buyer_name',
        'buyer_phone',
        'initial_offer_amount',
        'buyer_position',
        'financing_type',
        'mortgage_approval_status',
        'current_property_status',
        'proof_of_funds_document',
        'identity_document',
        'amount',
        'proof_document',
        'consent_to_terms',
    ];
    $bidModalShouldOpen = $property->online_offers_enabled
        && (session('offer_status') || session('offer_toast') || collect($bidErrorFields)->contains(fn (string $field): bool => $errors->has($field)));
@endphp

<x-layouts.site :agency="$property->agency" :title="$property->localizedTitle()">
    @if (session('offer_toast'))
        <div class="property-toast" data-auto-toast role="status" aria-live="polite">
            <span class="property-toast-icon" aria-hidden="true">✓</span>
            <span>{{ session('offer_toast') }}</span>
        </div>
    @endif

    @if ($galleryImages->isNotEmpty())
        <section class="photo-hero">
            <div class="shell">
                <div class="photo-browser" data-gallery>
                    <button class="photo-browser-main" type="button" data-gallery-open="0" aria-label="Open property photos">
                        <img src="{{ $galleryImages[0]['large'] }}" alt="{{ $galleryImages[0]['caption'] }}">
                        <span class="photo-browser-info">
                            <span>
                                <span class="photo-browser-title">{{ $property->localizedTitle() }}</span>
                            </span>
                            <span class="photo-count-pill">Browse all {{ $galleryImages->count() }} photos</span>
                        </span>
                    </button>

                    <div class="photo-browser-rail">
                        @foreach ($galleryImages as $index => $image)
                            <button class="photo-browser-thumb" type="button" data-gallery-open="{{ $index }}" aria-label="Open property photo {{ $index + 1 }}">
                                <img src="{{ $image['thumb'] }}" alt="{{ $image['caption'] }}">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="hero">
            <div class="shell">
                <span class="badge">{{ str_replace('_', ' ', $property->status) }}</span>
                <h1>{{ $property->localizedTitle() }}</h1>
                <p>{{ $location }}</p>
            </div>
        </section>
    @endif

    @if ($galleryImages->isNotEmpty())
        <div class="lightbox" data-lightbox aria-hidden="true">
            <div class="lightbox-bar">
                <strong data-lightbox-caption>{{ $galleryImages[0]['caption'] }}</strong>
                <button type="button" data-lightbox-close>Close</button>
            </div>
            <div class="lightbox-stage">
                <button class="lightbox-nav lightbox-prev" type="button" data-lightbox-prev aria-label="Previous photo">‹</button>
                <img data-lightbox-image src="{{ $galleryImages[0]['large'] }}" alt="{{ $galleryImages[0]['caption'] }}">
                <button class="lightbox-nav lightbox-next" type="button" data-lightbox-next aria-label="Next photo">›</button>
            </div>
            <div class="lightbox-filmstrip">
                @foreach ($galleryImages as $index => $image)
                    <button type="button" data-lightbox-go="{{ $index }}" @class(['is-active' => $index === 0])>
                        <img src="{{ $image['thumb'] }}" alt="{{ $image['caption'] }}">
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <section class="band">
        <div class="shell property-layout">
            <article>
                <div class="card property-summary">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="notice">{{ session('status') }}</div>
                        @endif
                        @if (session('offer_status'))
                            <div class="notice">{{ session('offer_status') }}</div>
                        @endif

                        <div class="summary-header">
                            <div class="summary-header-row">
                                <div class="summary-chip-row">
                                    <span class="badge">{{ $category }}</span>
                                    <span class="summary-status">{{ $statusLabel }}</span>
                                </div>
                                <div class="summary-tools" aria-label="Property tools">
                                    <button class="summary-tool" type="button" aria-label="Save property">♡</button>
                                    <button class="summary-tool" type="button" aria-label="Print property" data-print-property>⎙</button>
                                    <button class="summary-tool" type="button" aria-label="Share property" data-share-property>↗</button>
                                </div>
                            </div>
                            <h2 class="summary-address">{{ $summaryAddress ?: $address }}</h2>
                        </div>

                        <div class="summary-grid">
                            <div class="summary-price-card" aria-label="{{ $priceLabel }}">
                                <span class="summary-label">{{ $priceLabel }}</span>
                                <div class="summary-price-line">
                                    @if ($property->price)
                                        <span class="summary-currency">EUR</span>
                                        <strong class="summary-amount">{{ number_format($property->price) }}</strong>
                                    @else
                                        <strong class="summary-amount summary-poa">{{ __('site.properties.price_on_application') }}</strong>
                                    @endif
                                </div>
                                <div class="summary-price-notes">
                                    @if ($pricePeriod)
                                        <span>{{ $pricePeriod }}</span>
                                    @endif
                                    @if ($pricePerSqmLabel)
                                        <span>{{ $pricePerSqmLabel }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="summary-facts-grid">
                                @if ($property->bedrooms !== null)
                                    <div class="summary-fact">
                                        <span class="summary-fact-label">{{ __('site.labels.bedrooms') }}</span>
                                        <strong>{{ $property->bedrooms }}</strong>
                                    </div>
                                @endif
                                @if ($property->bathrooms !== null)
                                    <div class="summary-fact">
                                        <span class="summary-fact-label">{{ __('site.labels.bathrooms') }}</span>
                                        <strong>{{ $property->bathrooms }}</strong>
                                    </div>
                                @endif
                                @if ($floorAreaLabel)
                                    <div class="summary-fact">
                                        <span class="summary-fact-label">Area</span>
                                        <strong>{{ $floorAreaLabel }} m²</strong>
                                    </div>
                                @endif
                                @if ($berLabel)
                                    <div class="summary-fact summary-fact-ber">
                                        <span class="summary-fact-label">Energy</span>
                                        @if ($berAssetUrl)
                                            <img class="ber-rating-image" src="{{ $berAssetUrl }}" alt="BER {{ $berLabel }}" loading="lazy">
                                        @else
                                            <span class="ber-badge ber-exempt"><span>BER</span><strong>{{ $berLabel }}</strong></span>
                                        @endif
                                    </div>
                                @endif
                                <div class="summary-fact summary-fact-type">
                                    <span class="summary-fact-label">Type</span>
                                    <strong>{{ $propertyTypeLabel }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="summary-actions">
                            <button type="button" data-enquiry-open>{{ __('site.properties.enquire') }}</button>
                            @if ($property->online_offers_enabled)
                                <button class="button secondary" type="button" data-modal-open="bid-modal">{{ __('site.properties.make_offer') }}</button>
                            @endif
                            <a class="button secondary" href="{{ \App\Support\LocaleUrl::route('mortgages') }}">{{ __('site.nav.mortgages') }}</a>
                        </div>
                    </div>
                </div>

                <h2>{{ __('site.properties.description') }}</h2>
                @if ($descriptionHtml)
                    <div class="property-description">{!! $descriptionHtml !!}</div>
                @endif
                <div class="detail-copy">
                    <p>Set in {{ $location ?: 'a sought-after location' }}, this {{ strtolower($category) }} is presented with practical buyer information, key features, and viewing context in one place.</p>
                    <p>
                        @if ($property->bedrooms || $property->bathrooms || $property->floor_area_m2)
                            The accommodation profile includes
                            @if ($property->bedrooms)
                                {{ $property->bedrooms }} {{ __('site.labels.bedrooms') }}
                            @endif
                            @if ($property->bathrooms)
                                {{ $property->bedrooms ? ', ' : '' }}{{ $property->bathrooms }} {{ __('site.labels.bathrooms') }}
                            @endif
                            @if ($property->floor_area_m2)
                                {{ ($property->bedrooms || $property->bathrooms) ? ', and ' : '' }}approximately {{ $property->floor_area_m2 }} sqm
                            @endif
                            .
                        @else
                            The listing is structured for easy review, with location, price, media, and enquiry actions available from this page.
                        @endif
                        @if ($berLabel)
                            The current BER rating is {{ $berLabel }}.
                        @endif
                    </p>
                    @if ($property->localizedFeatures())
                        <p>Highlights include {{ collect($property->localizedFeatures())->take(3)->join(', ', ' and ') }}.</p>
                    @endif
                </div>

                @if ($property->localizedFeatures())
                    <h2 style="margin-top: 34px;">{{ __('site.properties.features') }}</h2>
                    <div class="feature-list">
                        @foreach ($property->localizedFeatures() as $feature)
                            <span class="feature-card" title="{{ $feature }}">
                                <span class="feature-icon">{{ \App\Support\PropertyOptions::featureIcon($feature) }}</span>
                                <span class="feature-text">{{ $feature }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside id="property-enquiry" class="property-side">
                @if ($enquirySent)
                    <div class="enquiry-success" role="status">
                        <strong>{{ session('status') }}</strong>
                        <span>Thank you. The agency will review your message and contact you soon.</span>
                    </div>
                @endif

                <details class="card enquiry-card enquiry-disclosure" data-enquiry-panel data-enquiry-autocollapse="{{ ($enquiryShouldOpen || $enquirySent) ? 'false' : 'true' }}" @if ($enquiryShouldOpen) open @endif>
                    <summary class="enquiry-summary">
                        <span class="enquiry-avatar">
                            @if ($enquiryAvatarUrl)
                                <img src="{{ $enquiryAvatarUrl }}" alt="{{ $enquiryAvatarAlt }}">
                            @else
                                {{ $enquiryInitials }}
                            @endif
                        </span>
                        <span class="enquiry-summary-copy">
                            <span>Interested in this property?</span>
                            <strong>Send {{ $enquiryContactName }} a message or request a viewing</strong>
                            @if ($enquiryContactRole)
                                <em>{{ $enquiryContactRole }}</em>
                            @endif
                        </span>
                        <span class="enquiry-summary-action">
                            <span class="enquiry-nudge-icon" aria-hidden="true"></span>
                            <span class="enquiry-action-open">Open <span class="enquiry-chevron">⌄</span></span>
                            <span class="enquiry-action-close">Close <span class="enquiry-chevron">⌄</span></span>
                        </span>
                    </summary>

                    <div class="enquiry-collapse-body" data-enquiry-body>
                        <form class="property-enquiry-form" method="POST" action="{{ \App\Support\LocaleUrl::route('properties.enquiries.store', ['property' => $property]) }}">
                            @csrf
                            <label class="span-2">{{ __('site.labels.name') }}
                                <input name="name" value="{{ old('name') }}" required>
                            </label>
                            <label>{{ __('site.labels.email') }}
                                <input name="email" type="email" value="{{ old('email') }}" required>
                            </label>
                            <label>{{ __('site.labels.phone') }}
                                <input name="phone" value="{{ old('phone') }}">
                            </label>
                            <fieldset class="enquiry-type-field span-2">
                            <legend>Enquiry type</legend>
                            <div class="enquiry-type-options">
                                @foreach ($enquiryTypes as $type => $label)
                                    <label class="enquiry-type-option">
                                        <input name="enquiry_type" type="radio" value="{{ $type }}" @checked(old('enquiry_type', 'question') === $type)>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                            <label class="span-2">{{ __('site.labels.message') }}
                                <textarea name="message" rows="4">{{ old('message', 'I would like more information about '.$property->localizedTitle().'.') }}</textarea>
                            </label>
                            <p class="enquiry-privacy span-2">Your details are used only to respond to this property enquiry.</p>
                            <button class="span-2" type="submit">{{ __('site.actions.send_enquiry') }}</button>
                        </form>
                    </div>
                </details>

                <div class="card map-card">
                    <iframe
                        class="map-frame"
                        title="Map for {{ $property->localizedTitle() }}"
                        src="{{ $mapUrl }}"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                    <div class="card-body">
                        <h2>Location</h2>
                        <p class="muted">{{ $address }}</p>
                        <div class="amenity-list">
                            @foreach ($nearbyAmenities as $amenity)
                                <div class="amenity-item">
                                    <span class="amenity-icon">{{ $amenity['icon'] }}</span>
                                    <span>
                                        <strong>{{ $amenity['type'] }}</strong><br>
                                        <span class="muted">{{ $amenity['name'] }}</span>
                                    </span>
                                    <span class="amenity-distance">{{ number_format($amenity['distance'], 1) }} km</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if ($relatedLinks->isNotEmpty())
        <section class="property-related-searches" aria-label="Related property searches">
            <div class="shell">
                <div class="property-related-header">
                    <h2>Related property searches</h2>
                    <p>Quick links based on this home's location and current asking price.</p>
                </div>
                <nav class="property-context-links" aria-label="Related property searches">
                    @foreach ($relatedLinks as $link)
                        <a class="property-context-link property-context-link-{{ $link['theme'] }}" href="{{ $link['url'] }}">
                            <span class="property-context-eyebrow">{{ $link['eyebrow'] }}</span>
                            <strong>{{ $link['label'] }}</strong>
                            <span>{{ $link['meta'] }}</span>
                            <span class="property-context-arrow" aria-hidden="true">↗</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </section>
    @endif

    @if ($property->online_offers_enabled)
        <div class="modal" id="bid-modal" aria-hidden="true">
            <div class="modal-card bid-access-modal">
                <div class="modal-head">
                    <div>
                        <span class="modal-eyebrow">Secure buyer verification</span>
                        <h2>Buyer bidding access</h2>
                    </div>
                    <button type="button" data-modal-close>Close</button>
                </div>
                <div class="modal-body">
                    <div class="bid-access-note">
                        <strong>Private bidding is account based.</strong>
                        <span>Create or sign in to a buyer account, submit proof documents, and wait for agency approval before an offer can be placed.</span>
                    </div>
                    <ol class="bid-access-flow" aria-label="Bidding access steps">
                        <li @class(['is-complete' => $buyerUser])><span>1</span>Buyer account</li>
                        <li @class(['is-complete' => $buyerAccessRequest])><span>2</span>Documents</li>
                        <li @class(['is-complete' => $buyerCanBid])><span>3</span>Agent approval</li>
                        <li @class(['is-complete' => false])><span>4</span>Submit offer</li>
                    </ol>

                    @if (session('offer_status'))
                        <div class="bid-alert">{{ session('offer_status') }}</div>
                    @endif
                    @if ($errors->any() && $bidModalShouldOpen)
                        <div class="bid-alert bid-alert-error">Please review the buyer account or bidding fields and try again.</div>
                    @endif

                    @if ($isStaffUser)
                        <div class="buyer-status-card">
                            <strong>Staff account detected</strong>
                            <p>You are signed in as an agency user. Buyer bidding access must be requested from a buyer account.</p>
                        </div>
                    @elseif ($buyerUser)
                        <div class="buyer-session-card">
                            <span>
                                <strong>{{ $buyerUser->name }}</strong>
                                <em>{{ $buyerUser->email }}</em>
                            </span>
                            <form method="POST" action="{{ \App\Support\LocaleUrl::route('buyer.logout') }}">
                                @csrf
                                <button class="button secondary" type="submit">Sign out</button>
                            </form>
                        </div>

                        @if ($buyerCanBid)
                            <div class="buyer-status-card buyer-status-approved">
                                <strong>Bidding access approved</strong>
                                <p>Your documents have been approved for this property. Submit an offer below for the agency to review.</p>
                            </div>
                            <form class="form bid-access-form buyer-offer-form" method="POST" action="{{ \App\Support\LocaleUrl::route('properties.offers.store', ['property' => $property]) }}" enctype="multipart/form-data">
                                @csrf
                                <input name="buyer_access_request_id" type="hidden" value="{{ $buyerAccessRequest->id }}">
                                <label class="bid-field">Offer amount
                                    <input name="amount" type="number" min="{{ $nextOfferAmount }}" step="{{ $bidIncrementAmount }}" value="{{ old('amount', $nextOfferAmount) }}" placeholder="EUR" required>
                                    <span class="bid-field-hint">
                                        Current price: EUR {{ number_format($currentOfferBaseAmount) }}.
                                        Next valid offer starts at EUR {{ number_format($nextOfferAmount) }} in EUR {{ number_format($bidIncrementAmount) }} steps.
                                    </span>
                                </label>
                                <label class="bid-field">Financing
                                    <select name="financing_type">
                                        <option value="">Select financing</option>
                                        @foreach (\App\Support\PropertyOptions::financingTypes() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('financing_type', $buyerAccessRequest->financing_type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="bid-field">Mortgage approval
                                    <select name="mortgage_approval_status">
                                        <option value="">Select status</option>
                                        <option value="approved_in_principle" @selected(old('mortgage_approval_status', $buyerAccessRequest->mortgage_approval_status) === 'approved_in_principle')>Approved in principle</option>
                                        <option value="pending" @selected(old('mortgage_approval_status', $buyerAccessRequest->mortgage_approval_status) === 'pending')>Pending</option>
                                        <option value="not_required" @selected(old('mortgage_approval_status', $buyerAccessRequest->mortgage_approval_status) === 'not_required')>Not required</option>
                                    </select>
                                </label>
                                <label class="bid-field">Updated proof document
                                    <input name="proof_document" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                </label>
                                <label class="span-2 bid-field">Offer note
                                    <textarea name="message" rows="2">{{ old('message') }}</textarea>
                                </label>
                                <label class="span-2 bid-consent">
                                    <input name="consent_to_terms" type="checkbox" value="1" required>
                                    <span>I confirm this offer is genuine and can be reviewed by the agency.</span>
                                </label>
                                <button class="span-2 bid-submit" type="submit">Submit offer for review</button>
                            </form>
                        @else
                            @if ($buyerAccessRequest)
                                <div class="buyer-status-card">
                                    <strong>Current access status: {{ $buyerAccessStatusLabel }}</strong>
                                    <p>
                                        @if ($buyerAccessStatus === 'pending_review')
                                            Your documents are waiting for agency review.
                                        @elseif ($buyerAccessStatus === 'pending_documents')
                                            Upload both proof of funds or mortgage approval and photo ID so the agency can review your request.
                                        @else
                                            The agency will review your buyer access before bidding opens.
                                        @endif
                                    </p>
                                    @if ($buyerAccessRequest?->initial_offer_amount)
                                        <p><strong>Current offer: EUR {{ number_format($buyerAccessRequest->initial_offer_amount) }}</strong></p>
                                    @endif
                                    <p><strong>Next valid offer: EUR {{ number_format($nextOfferAmount) }}</strong></p>
                                </div>
                            @endif
                            <form class="form bid-access-form" method="POST" action="{{ \App\Support\LocaleUrl::route('properties.buyer-access-requests.store', ['property' => $property]) }}" enctype="multipart/form-data">
                                @csrf
                                <label class="bid-field">Full name
                                    <input name="buyer_name" value="{{ old('buyer_name', $buyerAccessRequest?->buyer_name ?: $buyerUser->name) }}" autocomplete="name" required>
                                </label>
                                <label class="bid-field">{{ __('site.labels.email') }}
                                    <input type="email" value="{{ $buyerUser->email }}" readonly>
                                </label>
                                <label class="bid-field">{{ __('site.labels.phone') }}
                                    <input name="buyer_phone" value="{{ old('buyer_phone', $buyerAccessRequest?->buyer_phone) }}" autocomplete="tel">
                                </label>
                                <label class="bid-field">Offer amount
                                    <input name="initial_offer_amount" type="number" min="{{ $nextOfferAmount }}" step="{{ $bidIncrementAmount }}" value="{{ old('initial_offer_amount', $buyerInitialOfferValue) }}" placeholder="EUR" required>
                                    <span class="bid-field-hint">
                                        Start from EUR {{ number_format($nextOfferAmount) }}.
                                        Higher offers must increase by EUR {{ number_format($bidIncrementAmount) }}.
                                    </span>
                                </label>
                                <label class="bid-field">Buyer position
                                    <select name="buyer_position">
                                        <option value="">Select position</option>
                                        @foreach (\App\Support\PropertyOptions::buyerPositions() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('buyer_position', $buyerAccessRequest?->buyer_position) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="bid-field">Financing
                                    <select name="financing_type">
                                        <option value="">Select financing</option>
                                        @foreach (\App\Support\PropertyOptions::financingTypes() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('financing_type', $buyerAccessRequest?->financing_type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="bid-field">Mortgage approval
                                    <select name="mortgage_approval_status">
                                        <option value="">Select status</option>
                                        <option value="approved_in_principle" @selected(old('mortgage_approval_status', $buyerAccessRequest?->mortgage_approval_status) === 'approved_in_principle')>Approved in principle</option>
                                        <option value="pending" @selected(old('mortgage_approval_status', $buyerAccessRequest?->mortgage_approval_status) === 'pending')>Pending</option>
                                        <option value="not_required" @selected(old('mortgage_approval_status', $buyerAccessRequest?->mortgage_approval_status) === 'not_required')>Not required</option>
                                    </select>
                                </label>
                                <label class="bid-field">Proof of funds or mortgage approval
                                    <input name="proof_of_funds_document" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                </label>
                                <label class="bid-field">Photo ID
                                    <input name="identity_document" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                </label>
                                <label class="span-2 bid-field">Message
                                    <textarea name="message" rows="2">{{ old('message', $buyerAccessRequest?->message) }}</textarea>
                                </label>
                                <label class="span-2 bid-consent">
                                    <input name="consent_to_terms" type="checkbox" value="1" required>
                                    <span>I confirm the agency can review these details for bidding access.</span>
                                </label>
                                <button class="span-2 bid-submit" type="submit">{{ $buyerAccessRequest ? 'Update bidding access request' : 'Request bidding access' }}</button>
                            </form>
                        @endif
                    @else
                        <div class="buyer-account-grid" data-buyer-account-grid>
                            <form class="buyer-account-card buyer-register-card" method="POST" action="{{ \App\Support\LocaleUrl::route('buyer.register') }}" data-buyer-account-card="register">
                                @csrf
                                <span class="buyer-account-step">Step 1</span>
                                <h3>Create buyer account</h3>
                                <p>Use your email address as the account. Send a verification code first, then create the account.</p>
                                <div class="buyer-code-row">
                                    <label class="bid-field">Email
                                        <input name="buyer_register_email" type="email" value="{{ old('buyer_register_email') }}" autocomplete="email" required>
                                    </label>
                                    <button
                                        class="bid-inline-button"
                                        type="submit"
                                        formaction="{{ \App\Support\LocaleUrl::route('buyer.verification-code.store') }}"
                                        formnovalidate
                                        data-code-send-button
                                        data-code-cooldown="{{ (int) session('offer_code_cooldown_seconds', 0) }}"
                                    >Send code</button>
                                </div>
                                <label class="bid-field">Verification code
                                    <input name="buyer_register_verification_code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" value="{{ old('buyer_register_verification_code') }}" autocomplete="one-time-code" required>
                                </label>
                                <label class="bid-field">Password
                                    <input name="buyer_register_password" type="password" autocomplete="new-password" minlength="7" required>
                                </label>
                                <button class="bid-submit" type="submit">Create account</button>
                            </form>

                            <div class="buyer-account-card buyer-login-card" data-buyer-account-card="login">
                                <span class="buyer-account-step">Returning buyer</span>
                                <h3>Sign in</h3>
                                <p>Continue your verification request or submit an approved offer.</p>
                                <form class="buyer-login-form" method="POST" action="{{ \App\Support\LocaleUrl::route('buyer.login') }}">
                                    @csrf
                                    <label class="bid-field">Email
                                        <input name="buyer_login_email" type="email" value="{{ old('buyer_login_email') }}" autocomplete="email" required>
                                    </label>
                                    <label class="bid-field">Password
                                        <input name="buyer_login_password" type="password" autocomplete="current-password" required>
                                    </label>
                                    <button class="bid-submit" type="submit">Sign in</button>
                                </form>

                                <details class="buyer-password-reset" {{ $errors->has('buyer_reset_email') || $errors->has('buyer_reset_code') || $errors->has('buyer_reset_password') ? 'open' : '' }}>
                                    <summary>Forgot password?</summary>
                                    <p>Send a one-time code to your buyer email, then set a new password.</p>
                                    <form class="buyer-password-reset-form" method="POST" action="{{ \App\Support\LocaleUrl::route('buyer.password-reset-code.store') }}">
                                        @csrf
                                        <div class="buyer-code-row">
                                            <label class="bid-field">Buyer email
                                                <input name="buyer_reset_email" type="email" value="{{ old('buyer_reset_email') }}" autocomplete="email">
                                            </label>
                                            <button class="bid-inline-button" type="submit" formnovalidate>Send reset code</button>
                                        </div>
                                    </form>
                                    <form class="buyer-password-reset-form" method="POST" action="{{ \App\Support\LocaleUrl::route('buyer.password-reset') }}">
                                        @csrf
                                        <label class="bid-field">Buyer email
                                            <input name="buyer_reset_email" type="email" value="{{ old('buyer_reset_email') }}" autocomplete="email" required>
                                        </label>
                                        <label class="bid-field">Reset code
                                            <input name="buyer_reset_code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" value="{{ old('buyer_reset_code') }}" autocomplete="one-time-code" required>
                                        </label>
                                        <label class="bid-field">New password
                                            <input name="buyer_reset_password" type="password" autocomplete="new-password" minlength="7" required>
                                        </label>
                                        <label class="bid-field">Confirm new password
                                            <input name="buyer_reset_password_confirmation" type="password" autocomplete="new-password" minlength="7" required>
                                        </label>
                                        <button class="bid-submit" type="submit">Reset password</button>
                                    </form>
                                </details>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <script>
        (() => {
            const openModal = (id) => {
                const modal = document.getElementById(id);
                if (! modal) return;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };
            const closeModal = (modal) => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            };

            document.querySelectorAll('[data-modal-open]').forEach((button) => {
                button.addEventListener('click', () => openModal(button.dataset.modalOpen));
            });
            @if ($bidModalShouldOpen)
                openModal('bid-modal');
            @endif

            const autoToast = document.querySelector('[data-auto-toast]');

            if (autoToast) {
                window.setTimeout(() => {
                    autoToast.classList.add('is-leaving');
                    window.setTimeout(() => autoToast.remove(), 260);
                }, 3000);
            }

            document.querySelectorAll('[data-code-send-button]').forEach((button) => {
                const defaultLabel = button.textContent.trim();
                let remainingSeconds = Number(button.dataset.codeCooldown || 0);
                let cooldownTimer = null;

                const renderCooldown = () => {
                    if (remainingSeconds <= 0) {
                        button.disabled = false;
                        button.classList.remove('is-cooling-down');
                        button.textContent = defaultLabel;
                        window.clearInterval(cooldownTimer);

                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-cooling-down');
                    button.textContent = `${remainingSeconds}s`;
                    remainingSeconds -= 1;
                };

                button.form?.addEventListener('submit', (event) => {
                    if (event.submitter !== button || button.disabled) {
                        return;
                    }

                    button.disabled = true;
                    button.classList.add('is-cooling-down');
                    button.textContent = 'Sending...';
                });

                if (remainingSeconds > 0) {
                    renderCooldown();
                    cooldownTimer = window.setInterval(renderCooldown, 1000);
                }
            });

            const enquiryPanel = document.querySelector('[data-enquiry-panel]');
            const enquirySummary = enquiryPanel?.querySelector('.enquiry-summary');
            let enquiryAutoCollapseTimer = null;

            const cancelEnquiryAutoPreview = () => {
                if (! enquiryPanel) {
                    return;
                }

                enquiryPanel.dataset.enquiryPreview = 'false';
                window.clearTimeout(enquiryAutoCollapseTimer);
            };

            if (enquiryPanel && enquiryPanel.dataset.enquiryAutocollapse === 'true' && window.matchMedia('(min-width: 821px)').matches) {
                enquiryPanel.open = true;
                enquiryPanel.dataset.enquiryPreview = 'true';

                const scheduleEnquiryCollapse = () => {
                    enquiryAutoCollapseTimer = window.setTimeout(() => {
                        if (enquiryPanel.dataset.enquiryPreview !== 'true') {
                            return;
                        }

                        enquiryPanel.classList.add('is-rolling-up');
                        window.setTimeout(() => {
                            enquiryPanel.open = false;
                            enquiryPanel.classList.remove('is-rolling-up');
                            enquiryPanel.dataset.enquiryPreview = 'false';
                        }, 1850);
                    }, 1400);
                };

                if (document.readyState === 'complete') {
                    scheduleEnquiryCollapse();
                } else {
                    window.addEventListener('load', scheduleEnquiryCollapse, { once: true });
                }
            }

            enquirySummary?.addEventListener('click', cancelEnquiryAutoPreview);

            document.querySelectorAll('[data-enquiry-open]').forEach((button) => {
                button.addEventListener('click', () => {
                    const panel = document.querySelector('[data-enquiry-panel]');

                    if (! panel) {
                        return;
                    }

                    cancelEnquiryAutoPreview();
                    panel.open = true;
                    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

                    window.setTimeout(() => {
                        panel.querySelector('input[name="name"]')?.focus({ preventScroll: true });
                    }, 260);
                });
            });
            document.querySelectorAll('.modal').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal || event.target.closest('[data-modal-close]')) {
                        closeModal(modal);
                    }
                });
            });
            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                document.querySelectorAll('.modal.is-open').forEach(closeModal);
            });

            const buyerAccountGrid = document.querySelector('[data-buyer-account-grid]');
            const buyerAccountCards = Array.from(document.querySelectorAll('[data-buyer-account-card]'));

            const setActiveBuyerAccountCard = (activeCard) => {
                if (! buyerAccountGrid || ! activeCard) {
                    return;
                }

                buyerAccountGrid.dataset.activeAccountPanel = activeCard.dataset.buyerAccountCard || '';
                buyerAccountCards.forEach((card) => {
                    const isActive = card === activeCard;
                    card.classList.toggle('is-active', isActive);
                    card.classList.toggle('is-inactive', ! isActive);
                });
            };

            buyerAccountCards.forEach((card) => {
                card.addEventListener('focusin', () => setActiveBuyerAccountCard(card));
                card.addEventListener('input', () => setActiveBuyerAccountCard(card));
                card.addEventListener('pointerdown', () => setActiveBuyerAccountCard(card));
            });

            const filledBuyerAccountCard = buyerAccountCards.find((card) => (
                Array.from(card.querySelectorAll('input')).some((input) => String(input.value || '').trim() !== '')
            ));
            if (filledBuyerAccountCard) {
                setActiveBuyerAccountCard(filledBuyerAccountCard);
            }

            document.querySelectorAll('[data-print-property]').forEach((button) => {
                button.addEventListener('click', () => window.print());
            });

            document.querySelectorAll('[data-share-property]').forEach((button) => {
                button.addEventListener('click', async () => {
                    if (navigator.share) {
                        await navigator.share({
                            title: document.title,
                            url: window.location.href,
                        });

                        return;
                    }

                    await navigator.clipboard?.writeText(window.location.href);
                });
            });
        })();
    </script>

    @if ($galleryImages->isNotEmpty())
        <script>
            (() => {
                const images = @json($galleryImages);
                const lightbox = document.querySelector('[data-lightbox]');
                const image = document.querySelector('[data-lightbox-image]');
                const caption = document.querySelector('[data-lightbox-caption]');
                const filmstrip = [...document.querySelectorAll('[data-lightbox-go]')];
                let current = 0;

                const show = (index) => {
                    current = (index + images.length) % images.length;
                    image.src = images[current].large;
                    image.alt = images[current].caption;
                    caption.textContent = `${current + 1} / ${images.length} · ${images[current].caption}`;
                    filmstrip.forEach((button) => button.classList.toggle('is-active', Number(button.dataset.lightboxGo) === current));
                };

                const open = (index) => {
                    show(index);
                    lightbox.classList.add('is-open');
                    lightbox.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                };

                const close = () => {
                    lightbox.classList.remove('is-open');
                    lightbox.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                };

                document.querySelectorAll('[data-gallery-open]').forEach((button) => {
                    button.addEventListener('click', () => open(Number(button.dataset.galleryOpen)));
                });
                document.querySelector('[data-lightbox-close]').addEventListener('click', close);
                document.querySelector('[data-lightbox-prev]').addEventListener('click', () => show(current - 1));
                document.querySelector('[data-lightbox-next]').addEventListener('click', () => show(current + 1));
                filmstrip.forEach((button) => button.addEventListener('click', () => show(Number(button.dataset.lightboxGo))));
                lightbox.addEventListener('click', (event) => {
                    if (event.target === lightbox) close();
                });
                document.addEventListener('keydown', (event) => {
                    if (! lightbox.classList.contains('is-open')) return;
                    if (event.key === 'Escape') close();
                    if (event.key === 'ArrowLeft') show(current - 1);
                    if (event.key === 'ArrowRight') show(current + 1);
                });
            })();
        </script>
    @endif
</x-layouts.site>
