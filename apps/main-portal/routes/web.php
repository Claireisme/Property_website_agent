<?php

use App\Http\Middleware\SetLocale;
use App\Models\PortalEnquiry;
use App\Models\PortalProperty;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$siteRoutes = function (): void {
    Route::get('/', function () {
        $properties = PortalProperty::query()
            ->with(['agency', 'translations'])
            ->whereIn('status', ['available', 'under_offer', 'sale_agreed'])
            ->latest('source_updated_at')
            ->latest()
            ->take(9)
            ->get();

        return view('home', [
            'properties' => $properties,
        ]);
    })->name('home');

    Route::get('/properties', function (Request $request) {
        $properties = PortalProperty::query()
            ->with(['agency', 'translations'])
            ->whereIn('status', ['available', 'under_offer', 'sale_agreed'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where(function ($query) use ($request): void {
                    $query->where('title', 'like', '%'.$request->string('q').'%')
                        ->orWhere('town', 'like', '%'.$request->string('q').'%')
                        ->orWhere('county', 'like', '%'.$request->string('q').'%');
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('property_type', $request->string('type')))
            ->latest('source_updated_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('properties.index', [
            'properties' => $properties,
        ]);
    })->name('properties.index');

    Route::get('/properties/{property:slug}', function (PortalProperty $property) {
        abort_unless(in_array($property->status, ['available', 'under_offer', 'sale_agreed'], true), 404);

        return view('properties.show', [
            'property' => $property->load(['agency', 'translations']),
        ]);
    })->name('properties.show');

    Route::post('/properties/{property:slug}/enquiries', function (Request $request, PortalProperty $property) {
        abort_unless(in_array($property->status, ['available', 'under_offer', 'sale_agreed'], true), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        PortalEnquiry::query()->create($data + [
            'portal_agency_id' => $property->portal_agency_id,
            'portal_property_id' => $property->id,
            'source' => 'main_portal',
            'status' => 'new',
        ]);

        return back()->with('status', __('site.messages.enquiry_sent'));
    })->name('properties.enquiries.store');
};

$siteRoutes();

Route::prefix('{locale}')
    ->where(['locale' => implode('|', Locales::nonDefaultCodes())])
    ->middleware(SetLocale::class)
    ->name('localized.')
    ->group($siteRoutes);
