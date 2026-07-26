<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\FeedToken;
use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PropertyFeedApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_requires_a_valid_bearer_token(): void
    {
        $this->getJson('/api/feed/v1/properties')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthorized');
    }

    public function test_feed_returns_agency_and_properties_for_authorized_sync(): void
    {
        $plainTextToken = 'feed_test_token';

        FeedToken::query()->create([
            'name' => 'Test Portal',
            'token_hash' => FeedToken::hashToken($plainTextToken),
            'is_active' => true,
        ]);

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
            'website_domain' => 'test-agency.test',
            'psra_licence_number' => '001234',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_001',
            'title' => '3 Bed Semi-Detached House',
            'slug' => '3-bed-semi-detached-house',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'semi_detached',
            'price' => 450000,
            'price_qualifier' => 'asking_price',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'town' => 'Rathmines',
            'county' => 'Dublin',
            'features' => ['Gas fired central heating'],
            'published_at' => now(),
        ]);

        $property->translations()->create([
            'locale' => 'zh',
            'status' => 'machine_translated',
            'title' => '三居室半独立住宅',
            'description' => '中文房源描述',
            'features' => ['燃气中央供暖'],
            'source_hash' => PropertyTranslation::sourceHashFor($property),
            'translated_at' => now(),
        ]);

        DB::table('properties')
            ->where('id', $property->id)
            ->update(['features' => json_encode('Gas fired central heating, Parking')]);

        $this->withToken($plainTextToken)
            ->getJson('/api/feed/v1/properties')
            ->assertOk()
            ->assertJsonPath('agency.name', 'Test Agency')
            ->assertJsonPath('agency.psra_licence_number', '001234')
            ->assertJsonPath('properties.0.external_listing_id', 'prop_001')
            ->assertJsonPath('properties.0.address.summary', 'Rathmines, Dublin')
            ->assertJsonPath('properties.0.features.0', 'Gas fired central heating')
            ->assertJsonPath('properties.0.features.1', 'Parking')
            ->assertJsonPath('properties.0.translations.zh.title', '三居室半独立住宅')
            ->assertJsonPath('pagination.total', 1);
    }
}
