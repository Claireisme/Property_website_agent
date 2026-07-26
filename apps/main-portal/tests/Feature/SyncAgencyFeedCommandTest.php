<?php

namespace Tests\Feature;

use App\Models\PortalAgency;
use App\Models\PortalProperty;
use App\Models\PortalPropertyTranslation;
use App\Models\SyncRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncAgencyFeedCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_command_upserts_feed_listings(): void
    {
        Http::fake([
            'agency.test/api/feed/v1/properties' => Http::response([
                'agency' => [
                    'id' => 'agency_1',
                    'name' => 'Example Estate Agents',
                    'website_url' => 'https://agency.test',
                    'psra_licence_number' => '001234',
                ],
                'generated_at' => now()->toIso8601String(),
                'properties' => [
                    [
                        'external_listing_id' => 'prop_001',
                        'source_url' => 'https://agency.test/properties/prop_001',
                        'title' => '3 Bed Semi-Detached House',
                        'status' => 'available',
                        'transaction_type' => 'sale',
                        'property_type' => 'semi_detached',
                        'price' => 450000,
                        'bedrooms' => 3,
                        'bathrooms' => 2,
                        'address' => [
                            'summary' => 'Rathmines, Dublin',
                            'town' => 'Rathmines',
                            'county' => 'Dublin',
                        ],
                        'description' => 'Property description',
                        'features' => 'South-facing garden, High ceilings',
                        'translations' => [
                            'zh' => [
                                'status' => 'machine_translated',
                                'title' => '中文同步标题',
                                'description' => '中文同步描述',
                                'features' => '朝南花园, 高天花板',
                                'source_hash' => 'hash_001',
                                'translated_at' => now()->toIso8601String(),
                            ],
                        ],
                        'images' => [],
                        'online_offers_enabled' => true,
                        'updated_at' => now()->toIso8601String(),
                        'published_at' => now()->toIso8601String(),
                    ],
                ],
                'pagination' => [
                    'next_page_url' => null,
                ],
            ]),
        ]);

        PortalAgency::query()->create([
            'name' => 'Example Estate Agents',
            'website_url' => 'https://agency.test',
            'feed_url' => 'https://agency.test/api/feed/v1/properties',
            'api_token_encrypted' => 'secret-token',
            'status' => 'active',
        ]);

        $this->artisan('sync:agency-feed')
            ->assertExitCode(0);

        $this->assertDatabaseHas(PortalProperty::class, [
            'external_listing_id' => 'prop_001',
            'title' => '3 Bed Semi-Detached House',
            'status' => 'available',
            'town' => 'Rathmines',
            'online_offers_enabled' => true,
        ]);

        $property = PortalProperty::query()->firstWhere('external_listing_id', 'prop_001');

        $this->assertSame(['South-facing garden', 'High ceilings'], $property->features);

        $this->assertDatabaseHas(PortalPropertyTranslation::class, [
            'locale' => 'zh',
            'title' => '中文同步标题',
            'source_hash' => 'hash_001',
        ]);

        $translation = PortalPropertyTranslation::query()->firstWhere('locale', 'zh');

        $this->assertSame(['朝南花园', '高天花板'], $translation->features);

        $this->assertDatabaseHas(SyncRun::class, [
            'status' => 'success',
            'listings_seen' => 1,
            'listings_created' => 1,
        ]);
    }
}
