<?php

namespace Tests\Feature;

use App\Models\PortalAgency;
use App\Models\PortalEnquiry;
use App\Models\PortalProperty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PortalPropertyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_lists_synced_properties(): void
    {
        $agency = PortalAgency::query()->create([
            'name' => 'Example Estate Agents',
            'feed_url' => 'https://agency.test/api/feed/v1/properties',
            'api_token_encrypted' => 'token',
            'status' => 'active',
        ]);

        PortalProperty::query()->create([
            'portal_agency_id' => $agency->id,
            'external_listing_id' => 'prop_001',
            'title' => '3 Bed Semi-Detached House',
            'slug' => '3-bed-semi-detached-house-prop-001',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'semi_detached',
            'price' => 450000,
            'address_summary' => 'Rathmines, Dublin',
            'images' => [],
            'features' => [],
            'first_synced_at' => now(),
            'last_synced_at' => now(),
        ]);

        $this->get('/properties')
            ->assertOk()
            ->assertSee('3 Bed Semi-Detached House')
            ->assertSee('Example Estate Agents');
    }

    public function test_buyer_can_submit_a_portal_property_enquiry(): void
    {
        $agency = PortalAgency::query()->create([
            'name' => 'Example Estate Agents',
            'feed_url' => 'https://agency.test/api/feed/v1/properties',
            'api_token_encrypted' => 'token',
            'status' => 'active',
        ]);

        $property = PortalProperty::query()->create([
            'portal_agency_id' => $agency->id,
            'external_listing_id' => 'prop_001',
            'title' => '3 Bed Semi-Detached House',
            'slug' => '3-bed-semi-detached-house-prop-001',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'semi_detached',
            'price' => 450000,
            'address_summary' => 'Rathmines, Dublin',
            'images' => [],
            'features' => [],
            'first_synced_at' => now(),
            'last_synced_at' => now(),
        ]);

        $this->post(route('properties.enquiries.store', $property), [
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'message' => 'I would like to view this property.',
        ])->assertRedirect();

        $this->assertDatabaseHas(PortalEnquiry::class, [
            'portal_agency_id' => $agency->id,
            'portal_property_id' => $property->id,
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'source' => 'main_portal',
            'status' => 'new',
        ]);
    }

    public function test_property_detail_page_handles_legacy_string_features(): void
    {
        $agency = PortalAgency::query()->create([
            'name' => 'Example Estate Agents',
            'feed_url' => 'https://agency.test/api/feed/v1/properties',
            'api_token_encrypted' => 'token',
            'status' => 'active',
        ]);

        $property = PortalProperty::query()->create([
            'portal_agency_id' => $agency->id,
            'external_listing_id' => 'prop_001',
            'title' => '3 Bed Semi-Detached House',
            'slug' => '3-bed-semi-detached-house-prop-001',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'semi_detached',
            'price' => 450000,
            'address_summary' => 'Rathmines, Dublin',
            'images' => [],
            'features' => [],
            'first_synced_at' => now(),
            'last_synced_at' => now(),
        ]);

        DB::table('portal_properties')
            ->where('id', $property->id)
            ->update(['features' => json_encode('Parking,Central Heating')]);

        $this->get('/properties/3-bed-semi-detached-house-prop-001')
            ->assertOk()
            ->assertSee('Parking')
            ->assertSee('Central Heating');
    }

    public function test_non_english_portal_property_page_shows_disclaimer_and_translation(): void
    {
        $agency = PortalAgency::query()->create([
            'name' => 'Example Estate Agents',
            'feed_url' => 'https://agency.test/api/feed/v1/properties',
            'api_token_encrypted' => 'token',
            'status' => 'active',
        ]);

        $property = PortalProperty::query()->create([
            'portal_agency_id' => $agency->id,
            'external_listing_id' => 'prop_001',
            'title' => '3 Bed Semi-Detached House',
            'slug' => '3-bed-semi-detached-house-prop-001',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'semi_detached',
            'price' => 450000,
            'address_summary' => 'Rathmines, Dublin',
            'description' => 'English portal description',
            'images' => [],
            'features' => ['English portal feature'],
            'first_synced_at' => now(),
            'last_synced_at' => now(),
        ]);

        $property->translations()->create([
            'locale' => 'zh',
            'status' => 'machine_translated',
            'title' => '中文门户标题',
            'description' => '中文门户描述',
            'features' => ['中文门户特点'],
            'source_hash' => 'hash_001',
            'translated_at' => now(),
        ]);

        $this->get('/zh/properties/3-bed-semi-detached-house-prop-001')
            ->assertOk()
            ->assertSee('翻译提示')
            ->assertSee('中文门户标题')
            ->assertSee('中文门户描述')
            ->assertSee('中文门户特点');
    }
}
