<?php

namespace Tests\Feature;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Agency;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyTranslation;
use App\Support\PropertyOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedAgencySiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_english_property_page_shows_disclaimer_and_translation(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
            'county' => 'Dublin',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_localized',
            'title' => 'English Property Title',
            'slug' => 'english-property-title',
            'status' => 'available',
            'description' => 'English description',
            'features' => ['English feature'],
            'published_at' => now(),
        ]);

        $property->translations()->create([
            'locale' => 'zh',
            'status' => 'machine_translated',
            'title' => '中文房源标题',
            'description' => '中文房源描述',
            'features' => ['中文特点'],
            'source_hash' => PropertyTranslation::sourceHashFor($property),
            'translated_at' => now(),
        ]);

        $this->get('/zh/properties/english-property-title')
            ->assertOk()
            ->assertSee('翻译提示')
            ->assertSee('中文房源标题')
            ->assertSee('中文房源描述')
            ->assertSee('中文特点');
    }

    public function test_fake_translation_command_creates_test_translations_without_api_key(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_fake_translate',
            'title' => 'Fake Translate Property',
            'slug' => 'fake-translate-property',
            'status' => 'available',
            'description' => 'English description',
            'features' => ['Garden'],
            'published_at' => now(),
        ]);

        $this->artisan('properties:translate', [
            '--fake' => true,
            '--locale' => ['zh'],
        ])->assertExitCode(0);

        $this->assertDatabaseHas('property_translations', [
            'property_id' => $property->id,
            'locale' => 'zh',
            'status' => 'test_placeholder',
            'title' => '[ZH test] Fake Translate Property',
        ]);
    }

    public function test_home_page_shows_google_reviews_section(): void
    {
        Agency::query()->create([
            'name' => 'Test Agency',
            'county' => 'Dublin',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Excellent')
            ->assertSee('Based on')
            ->assertSee('162 reviews')
            ->assertSee('Eamonn Ward')
            ->assertSee('Philip Hughes')
            ->assertSee('Goncalo Dias')
            ->assertSee('Mary O&#039;Connor', false)
            ->assertSee('Ronan Walsh')
            ->assertSee('google-review-marquee', false);
    }

    public function test_site_footer_shows_company_licence_contact_and_social_links(): void
    {
        Agency::query()->create([
            'name' => 'Footer Estates Limited',
            'trading_name' => 'Footer Estates',
            'company_registration_number' => '654321',
            'psra_licence_number' => '009999',
            'phone' => '+353 1 555 0199',
            'email' => 'hello@footer-estates.test',
            'address' => '88 Footer Street',
            'county' => 'Dublin',
            'eircode' => 'D08 FOOT',
            'facebook_url' => 'https://www.facebook.com/footerestates',
            'instagram_url' => 'https://www.instagram.com/footerestates',
            'youtube_url' => 'https://www.youtube.com/@footerestates',
            'tiktok_url' => 'https://www.tiktok.com/@footerestates',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Footer Estates Limited')
            ->assertSee('Company No.')
            ->assertSee('654321')
            ->assertSee('PSRA Number')
            ->assertSee('009999')
            ->assertSee('+353 1 555 0199')
            ->assertSee('hello@footer-estates.test')
            ->assertSee('88 Footer Street, Dublin, D08 FOOT')
            ->assertSee('https://www.youtube.com/@footerestates')
            ->assertSee('https://www.tiktok.com/@footerestates')
            ->assertSee('https://www.instagram.com/footerestates')
            ->assertSee('https://www.facebook.com/footerestates')
            ->assertSee('footer-social-icon footer-social-icon--youtube', false)
            ->assertSee('footer-social-icon footer-social-icon--tiktok', false)
            ->assertDontSee('>YT<', false)
            ->assertDontSee('>TT<', false)
            ->assertSee('A note from our managing director')
            ->assertSee('Patrick Doyle')
            ->assertSee('images/team/patrick-doyle.jpg')
            ->assertSee('Every client deserves straight advice');
    }

    public function test_properties_index_can_filter_by_listing_category(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_sale',
            'title' => 'Residential Sale',
            'slug' => 'residential-sale',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'house',
            'published_at' => now(),
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_commercial',
            'title' => 'Commercial Office',
            'slug' => 'commercial-office',
            'status' => 'available',
            'transaction_type' => 'rent',
            'property_type' => 'office',
            'published_at' => now(),
        ]);

        $this->get('/properties?category=commercial')
            ->assertOk()
            ->assertSee('Commercial Office')
            ->assertDontSee('Residential Sale');
    }

    public function test_properties_index_filters_sub_regions_without_returning_the_whole_county(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_dublin_6_region',
            'title' => 'Dublin 6 Region Home',
            'slug' => 'dublin-6-region-home',
            'status' => 'available',
            'town' => 'Dublin 6',
            'county' => 'Dublin',
            'published_at' => now(),
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_blackrock_region',
            'title' => 'Blackrock Region Home',
            'slug' => 'blackrock-region-home',
            'status' => 'available',
            'town' => 'Blackrock',
            'county' => 'Dublin',
            'published_at' => now(),
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_cork_region',
            'title' => 'Cork Region Home',
            'slug' => 'cork-region-home',
            'status' => 'available',
            'town' => 'Cork City',
            'county' => 'Cork',
            'published_at' => now(),
        ]);

        $this->get('/properties?region=dublin_6')
            ->assertOk()
            ->assertSee('Dublin 6 Region Home')
            ->assertDontSee('Blackrock Region Home')
            ->assertDontSee('Cork Region Home');

        $this->get('/properties?region=dublin')
            ->assertOk()
            ->assertSee('Dublin 6 Region Home')
            ->assertSee('Blackrock Region Home')
            ->assertDontSee('Cork Region Home');
    }

    public function test_about_page_shows_company_profile_and_team(): void
    {
        Agency::query()->create([
            'name' => 'Test Agency',
            'county' => 'Dublin',
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Test Agency')
            ->assertSee('Meet the people behind the listings.')
            ->assertSee('Patrick Doyle')
            ->assertSee('Aoife Byrne')
            ->assertSee('images/team/patrick-doyle.jpg');

        $this->get('/properties')
            ->assertOk()
            ->assertSee('About us')
            ->assertSeeInOrder(['Properties', 'Valuation', 'Mortgages', 'About us', 'Contact'])
            ->assertDontSee('Admin');
    }

    public function test_properties_index_uses_simplified_facility_filters(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_gas_heat',
            'title' => 'Gas Heat Home',
            'slug' => 'gas-heat-home',
            'status' => 'available',
            'facilities' => ['gas_heating'],
            'published_at' => now(),
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_oil_heat',
            'title' => 'Oil Heat Home',
            'slug' => 'oil-heat-home',
            'status' => 'available',
            'facilities' => ['oil_heating'],
            'published_at' => now(),
        ]);

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_no_heat',
            'title' => 'No Heat Home',
            'slug' => 'no-heat-home',
            'status' => 'available',
            'facilities' => ['parking'],
            'published_at' => now(),
        ]);

        $this->get('/properties')
            ->assertOk()
            ->assertSee('Heating')
            ->assertDontSee('Gas heating')
            ->assertDontSee('Oil heating')
            ->assertDontSee('Wheelchair access');

        $this->get('/properties?facilities%5B0%5D=heating')
            ->assertOk()
            ->assertSee('Gas Heat Home')
            ->assertSee('Oil Heat Home')
            ->assertDontSee('No Heat Home');
    }

    public function test_property_page_displays_grouped_ber_image_badge(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_ber_badge',
            'title' => 'BER Badge Home',
            'slug' => 'ber-badge-home',
            'status' => 'available',
            'ber_rating' => 'B3',
            'published_at' => now(),
        ]);

        $this->assertSame('B', $property->refresh()->ber_rating);
        $this->assertSame('B', PropertyOptions::normalizeBerRating('B2'));
        $this->assertSame('A0', PropertyOptions::normalizeBerRating('A0'));
        $this->assertSame('b', PropertyOptions::berAssetLevel('B3'));
        $this->assertSame([
            'A0' => 'A0',
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'Exempt' => 'Exempt',
        ], PropertyOptions::berRatings());

        $this->get('/properties/ber-badge-home')
            ->assertOk()
            ->assertSee('images/ber/ber-b.png')
            ->assertSee('alt="BER B"', false)
            ->assertDontSee('BER B3');
    }

    public function test_property_page_shows_related_area_and_price_search_links(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_related_links',
            'title' => 'Related Link Property',
            'slug' => 'related-link-property',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'semi_detached',
            'price' => 1150000,
            'town' => 'Dublin 6',
            'county' => 'Dublin',
            'published_at' => now(),
        ]);

        PropertyImage::withoutEvents(function () use ($property): void {
            PropertyImage::query()->create([
                'property_id' => $property->id,
                'original_url' => 'properties/test/original.jpg',
                'large_url' => 'properties/test/large.jpg',
                'thumbnail_url' => 'properties/test/thumb.jpg',
                'caption' => 'Front exterior',
            ]);
        });

        $this->get('/properties/related-link-property')
            ->assertOk()
            ->assertSeeInOrder(['Description', 'Related property searches'])
            ->assertSee('Related property searches')
            ->assertSee('Properties in Dublin 6')
            ->assertSee('Dublin listings')
            ->assertSee('EUR 1,035,000 - 1,265,000')
            ->assertSee('Within 10% in Dublin')
            ->assertSee('property-context-link-area', false)
            ->assertSee('property-context-link-budget', false)
            ->assertSee('region=dublin_6', false)
            ->assertSee('region=dublin', false)
            ->assertSee('min_price=1035000', false)
            ->assertSee('max_price=1265000', false)
            ->assertDontSee('Message from the managing director');
    }

    public function test_properties_index_shows_newly_created_property_before_older_published_property(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $older = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_older_published',
            'title' => 'Older Published Home',
            'slug' => 'older-published-home',
            'status' => 'available',
            'published_at' => now()->subDay(),
        ]);
        $older->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_fresh_added',
            'title' => 'Fresh Added Home',
            'slug' => 'fresh-added-home',
            'status' => 'available',
            'published_at' => null,
        ]);

        $this->get('/properties')
            ->assertOk()
            ->assertSeeInOrder(['Fresh Added Home', 'Older Published Home']);
    }

    public function test_property_page_handles_blank_feature_string_from_admin_form(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_blank_features',
            'title' => 'Blank Feature Property',
            'slug' => 'blank-feature-property',
            'status' => 'available',
            'description' => 'A listing with no feature chips yet.',
        ]);
        $property->forceFill(['features' => ''])->save();

        $this->get('/properties/blank-feature-property')
            ->assertOk()
            ->assertSee('Blank Feature Property')
            ->assertDontSee('TypeError');
    }

    public function test_admin_property_resource_uses_slug_route_keys(): void
    {
        $this->assertSame('slug', PropertyResource::getRecordRouteKeyName());
    }
}
