<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyFeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $agency = Agency::query()->firstOrFail();
        $perPage = min((int) $request->integer('per_page', 100), 100);
        $properties = Property::query()
            ->with(['images', 'translations'])
            ->whereNotIn('status', ['draft', 'archived'])
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'agency' => [
                'id' => 'agency_'.$agency->id,
                'name' => $agency->name,
                'website_url' => url('/'),
                'psra_licence_number' => $agency->psra_licence_number,
            ],
            'generated_at' => now()->toIso8601String(),
            'properties' => $properties->getCollection()
                ->map(fn (Property $property): array => $this->serializeProperty($property))
                ->values(),
            'pagination' => [
                'page' => $properties->currentPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
                'next_page_url' => $properties->nextPageUrl(),
            ],
        ]);
    }

    private function serializeProperty(Property $property): array
    {
        return [
            'external_listing_id' => $property->public_id,
            'source_url' => route('properties.show', $property),
            'title' => $property->title,
            'status' => $property->status,
            'transaction_type' => $property->transaction_type,
            'property_type' => $property->property_type,
            'price' => $property->price,
            'price_qualifier' => $property->price_qualifier,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'floor_area_m2' => $property->floor_area_m2,
            'ber_rating' => $property->ber_rating,
            'address' => [
                'summary' => collect([$property->town, $property->county])->filter()->join(', '),
                'line_1' => $property->address_line_1,
                'town' => $property->town,
                'county' => $property->county,
                'eircode' => $property->eircode,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
            ],
            'description' => $property->description,
            'features' => Property::normalizeFeatureList($property->features),
            'facilities' => Property::normalizeFeatureList($property->facilities),
            'translations' => $property->translations
                ->reject(fn ($translation): bool => $translation->status === 'failed')
                ->mapWithKeys(fn ($translation): array => [
                    $translation->locale => [
                        'status' => $translation->status,
                        'title' => $translation->title,
                        'description' => $translation->description,
                        'features' => Property::normalizeFeatureList($translation->features),
                        'viewing_notes' => $translation->viewing_notes,
                        'source_hash' => $translation->source_hash,
                        'translated_at' => $translation->translated_at?->toIso8601String(),
                    ],
                ])
                ->all(),
            'images' => $property->images
                ->map(fn ($image): array => [
                    'url' => $image->canonicalUrl($image->large_url ?: $image->original_url),
                    'thumbnail_url' => $image->canonicalUrl($image->thumbnail_url ?: $image->original_url),
                    'caption' => $image->caption,
                    'sort_order' => $image->sort_order,
                ])
                ->values(),
            'open_viewings' => [],
            'online_offers_enabled' => $property->online_offers_enabled,
            'updated_at' => $property->updated_at?->toIso8601String(),
            'published_at' => $property->published_at?->toIso8601String(),
        ];
    }

}
