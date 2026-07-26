<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\BuyerAccessRequest;
use App\Models\EmailSetting;
use App\Models\Enquiry;
use App\Models\FeedToken;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Services\PropertyImageVariantGenerator;
use App\Support\BidIncrementRules;
use App\Support\EmailNotificationCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Agency Admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ],
        );

        FeedToken::query()->updateOrCreate(
            ['name' => 'Local Main Portal'],
            [
                'token_hash' => FeedToken::hashToken('dev-feed-token'),
                'is_active' => true,
                'expires_at' => null,
            ],
        );

        EmailSetting::query()->firstOrCreate([], [
            'mail_enabled' => true,
            'provider' => 'ses_smtp',
            'from_name' => 'Estate Agents Main',
            'from_email' => 'hello@example-estates.test',
            'reply_to_email' => 'hello@example-estates.test',
            'ses_region' => 'eu-west-1',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'notification_toggles' => EmailNotificationCatalog::defaultEnabledKeys(),
        ]);

        EmailNotificationCatalog::syncDefaults();

        $agency = Agency::query()->updateOrCreate(
            ['name' => 'Example Estate Agents'],
            [
                'trading_name' => 'Example Estates',
                'company_registration_number' => '765432',
                'psra_licence_number' => '001234',
                'website_domain' => 'example-estates.test',
                'primary_colour' => '#0f766e',
                'secondary_colour' => '#111827',
                'phone' => '+353 1 555 0100',
                'email' => 'hello@example-estates.test',
                'address' => '12 Main Street',
                'county' => 'Dublin',
                'eircode' => 'D02 TEST',
                'description' => 'Independent estate agents serving Dublin buyers and sellers.',
                'theme_key' => 'classic',
                'bid_increment_rules' => BidIncrementRules::defaults(),
                'facebook_url' => 'https://www.facebook.com/exampleestates',
                'instagram_url' => 'https://www.instagram.com/exampleestates',
                'youtube_url' => 'https://www.youtube.com/@exampleestates',
                'tiktok_url' => 'https://www.tiktok.com/@exampleestates',
                'linkedin_url' => 'https://www.linkedin.com/company/example-estates',
                'x_url' => 'https://x.com/exampleestates',
            ],
        );

        $teamMembers = collect($this->teamMembers($agency))
            ->map(function (array $teamMember) use ($agency): TeamMember {
                $user = User::query()->updateOrCreate(
                    ['email' => $teamMember['email']],
                    [
                        'name' => $teamMember['name'],
                        'role' => 'agent',
                        'password' => Hash::make('password'),
                    ],
                );

                return TeamMember::query()->updateOrCreate(
                    ['agency_id' => $agency->id, 'email' => $teamMember['email']],
                    $teamMember + ['user_id' => $user->id],
                );
            });

        $properties = collect($this->properties($agency, $teamMembers))
            ->map(function (array $propertyData): Property {
                $property = Property::query()->updateOrCreate(
                    ['public_id' => $propertyData['public_id']],
                    $propertyData,
                );

                foreach (range(1, 12) as $sortOrder) {
                    $imagePath = $this->ensureDemoImage($property, $sortOrder);
                    $propertyImage = PropertyImage::query()->updateOrCreate(
                        ['property_id' => $property->id, 'sort_order' => $sortOrder],
                        [
                            'original_url' => $imagePath,
                            'caption' => $property->title.' - photo '.$sortOrder,
                        ],
                    );

                    app(PropertyImageVariantGenerator::class)->generate($propertyImage);
                }

                return $property;
            });

        $rathmines = $properties->firstWhere('public_id', 'prop_demo_rathmines');
        $clontarf = $properties->firstWhere('public_id', 'prop_demo_clontarf');

        Enquiry::query()->updateOrCreate(
            ['email' => 'buyer.one@example.com', 'property_id' => $rathmines->id],
            [
                'name' => 'Buyer One',
                'phone' => '+353 86 555 1001',
                'enquiry_type' => 'viewing',
                'message' => 'I would like to arrange a viewing this week.',
                'source' => 'agency_site',
                'status' => 'new',
            ],
        );

        Enquiry::query()->updateOrCreate(
            ['email' => 'relocator@example.com', 'property_id' => $clontarf->id],
            [
                'name' => 'Aoife Murphy',
                'phone' => '+353 87 555 1002',
                'enquiry_type' => 'question',
                'message' => 'Is there an open viewing scheduled for Saturday?',
                'source' => 'main_portal',
                'status' => 'contacted',
            ],
        );

        ValuationRequest::query()->updateOrCreate(
            ['email' => 'seller@example.com', 'property_address' => '8 Oak Avenue, Terenure'],
            [
                'name' => 'Michael OBrien',
                'phone' => '+353 85 555 2001',
                'eircode' => 'D6W TEST',
                'property_type' => 'terraced',
                'bedrooms' => 3,
                'bathrooms' => 1,
                'preferred_contact_method' => 'phone',
                'selling_timeline' => '3_6_months',
                'message' => 'We are considering selling later this year.',
                'source' => 'agency_site',
                'status' => 'new',
            ],
        );

        ValuationRequest::query()->updateOrCreate(
            ['email' => 'owner@example.com', 'property_address' => 'Apartment 14, Grand Canal Dock'],
            [
                'name' => 'Claire Walsh',
                'phone' => '+353 85 555 2002',
                'eircode' => 'D04 TEST',
                'property_type' => 'apartment',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'preferred_contact_method' => 'email',
                'selling_timeline' => 'just_researching',
                'message' => 'Please send an indicative valuation range.',
                'source' => 'main_portal',
                'status' => 'contacted',
            ],
        );

        $buyerAccessRequest = BuyerAccessRequest::query()->updateOrCreate(
            ['property_id' => $rathmines->id, 'buyer_email' => 'offer.buyer@example.com'],
            [
                'buyer_name' => 'Niamh Kelly',
                'buyer_phone' => '+353 86 555 3001',
                'status' => 'approved',
                'initial_offer_amount' => 462500,
                'buyer_position' => 'first_time_buyer',
                'financing_type' => 'mortgage',
                'mortgage_approval_status' => 'approved_in_principle',
                'current_property_status' => 'renting',
                'message' => 'I am ready to bid once documents are approved.',
                'consent_to_terms' => true,
                'requested_at' => now()->subDay(),
                'documents_uploaded_at' => now()->subDay()->addHour(),
                'reviewed_at' => now()->subHours(8),
                'approved_at' => now()->subHours(8),
            ],
        );

        $offer = Offer::query()->updateOrCreate(
            ['property_id' => $rathmines->id, 'buyer_email' => 'offer.buyer@example.com'],
            [
                'buyer_access_request_id' => $buyerAccessRequest->id,
                'buyer_name' => 'Niamh Kelly',
                'buyer_phone' => '+353 86 555 3001',
                'amount' => 462500,
                'status' => 'pending_review',
                'buyer_position' => 'first_time_buyer',
                'financing_type' => 'mortgage',
                'mortgage_approval_status' => 'approved_in_principle',
                'current_property_status' => 'renting',
                'conditions' => ['Subject to survey', 'Subject to loan offer'],
                'message' => 'Mortgage approval in principle is ready.',
                'consent_to_terms' => true,
                'submitted_at' => now()->subHours(6),
            ],
        );

        OfferEvent::query()->updateOrCreate(
            ['offer_id' => $offer->id, 'event_type' => 'offer_submitted'],
            [
                'actor_type' => 'buyer',
                'metadata' => [
                    'amount' => $offer->amount,
                    'financing_type' => $offer->financing_type,
                ],
            ],
        );
    }

    private function teamMembers(Agency $agency): array
    {
        return [
            [
                'agency_id' => $agency->id,
                'name' => 'Sarah Byrne',
                'role' => 'Managing Agent',
                'email' => 'sarah@example-estates.test',
                'phone' => '+353 1 555 0101',
                'bio' => 'Sarah leads residential sales and valuations across Dublin.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'agency_id' => $agency->id,
                'name' => 'Conor Walsh',
                'role' => 'Senior Negotiator',
                'email' => 'conor@example-estates.test',
                'phone' => '+353 1 555 0102',
                'bio' => 'Conor manages buyer viewings and offer negotiations.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'agency_id' => $agency->id,
                'name' => 'Emma Nolan',
                'role' => 'Valuations Manager',
                'email' => 'emma@example-estates.test',
                'phone' => '+353 1 555 0103',
                'bio' => 'Emma handles market appraisals and vendor onboarding.',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];
    }

    private function properties(Agency $agency, $teamMembers): array
    {
        $base = [
            [
                'agency_id' => $agency->id,
                'public_id' => 'prop_demo_rathmines',
                'title' => '3 Bed Semi-Detached House in Rathmines',
                'slug' => '3-bed-semi-detached-house-rathmines',
                'status' => 'available',
                'transaction_type' => 'sale',
                'property_type' => 'semi_detached',
                'price' => 450000,
                'price_qualifier' => 'asking_price',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'floor_area_m2' => 112,
                'ber_rating' => 'B',
                'address_line_1' => 'Hidden from portal if needed',
                'town' => 'Rathmines',
                'county' => 'Dublin',
                'eircode' => 'D06 TEST',
                'latitude' => 53.321,
                'longitude' => -6.265,
                'description' => 'A bright family home close to village amenities, schools, transport links, and mature green spaces.',
                'features' => ['Gas fired central heating', 'South-facing garden', 'Off-street parking'],
                'facilities' => ['parking', 'garden', 'gas_heating'],
                'online_offers_enabled' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'agency_id' => $agency->id,
                'public_id' => 'prop_demo_clontarf',
                'title' => '4 Bed Period Home near Clontarf Seafront',
                'slug' => '4-bed-period-home-clontarf-seafront',
                'status' => 'under_offer',
                'transaction_type' => 'sale',
                'property_type' => 'terraced',
                'price' => 785000,
                'price_qualifier' => 'guide_price',
                'bedrooms' => 4,
                'bathrooms' => 2,
                'floor_area_m2' => 156,
                'ber_rating' => 'C',
                'town' => 'Clontarf',
                'county' => 'Dublin',
                'eircode' => 'D03 TEST',
                'latitude' => 53.363,
                'longitude' => -6.195,
                'description' => 'A carefully restored period home with generous reception rooms and easy access to the coast.',
                'features' => ['Original fireplaces', 'High ceilings', 'Private rear garden'],
                'facilities' => ['garden', 'alarm', 'sea_view'],
                'online_offers_enabled' => false,
                'published_at' => now()->subDays(4),
            ],
            [
                'agency_id' => $agency->id,
                'public_id' => 'prop_demo_galway',
                'title' => '2 Bed Apartment overlooking Galway Bay',
                'slug' => '2-bed-apartment-galway-bay',
                'status' => 'sale_agreed',
                'transaction_type' => 'sale',
                'property_type' => 'apartment',
                'price' => 365000,
                'price_qualifier' => 'asking_price',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'floor_area_m2' => 82,
                'ber_rating' => 'B',
                'town' => 'Salthill',
                'county' => 'Galway',
                'eircode' => 'H91 TEST',
                'latitude' => 53.260,
                'longitude' => -9.083,
                'description' => 'A modern apartment with lift access, balcony space, and sea views toward Galway Bay.',
                'features' => ['Balcony', 'Secure parking', 'Lift access'],
                'facilities' => ['balcony', 'parking', 'lift', 'sea_view'],
                'online_offers_enabled' => true,
                'published_at' => now()->subDays(8),
                'sale_agreed_at' => now()->subDay(),
            ],
            [
                'agency_id' => $agency->id,
                'public_id' => 'prop_demo_cork_rental',
                'title' => '1 Bed City Apartment to Rent in Cork',
                'slug' => '1-bed-city-apartment-rent-cork',
                'status' => 'available',
                'transaction_type' => 'rent',
                'property_type' => 'apartment',
                'price' => 1850,
                'price_qualifier' => 'asking_price',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'floor_area_m2' => 48,
                'ber_rating' => 'B',
                'town' => 'Cork City',
                'county' => 'Cork',
                'eircode' => 'T12 TEST',
                'latitude' => 51.898,
                'longitude' => -8.475,
                'description' => 'A well located city apartment close to offices, restaurants, and public transport.',
                'features' => ['Furnished', 'Managed building', 'Excellent transport links'],
                'facilities' => ['furnished', 'lift'],
                'online_offers_enabled' => false,
                'published_at' => now()->subHours(18),
            ],
            [
                'agency_id' => $agency->id,
                'public_id' => 'prop_demo_kilkenny',
                'title' => 'Detached Country House outside Kilkenny',
                'slug' => 'detached-country-house-kilkenny',
                'status' => 'sold',
                'transaction_type' => 'sale',
                'property_type' => 'detached',
                'price' => 620000,
                'price_qualifier' => 'guide_price',
                'bedrooms' => 5,
                'bathrooms' => 3,
                'floor_area_m2' => 224,
                'ber_rating' => 'C',
                'town' => 'Kilkenny',
                'county' => 'Kilkenny',
                'eircode' => 'R95 TEST',
                'latitude' => 52.654,
                'longitude' => -7.244,
                'description' => 'A substantial detached home on mature grounds with a separate garage and countryside views.',
                'features' => ['Mature grounds', 'Detached garage', 'Oil fired central heating'],
                'facilities' => ['garden', 'parking', 'oil_heating'],
                'online_offers_enabled' => false,
                'published_at' => now()->subDays(20),
                'sold_at' => now()->subDays(3),
            ],
        ];

        $properties = array_merge($base, $this->syntheticProperties($agency));
        $teamMembers = $teamMembers->values();

        if ($teamMembers->isNotEmpty()) {
            foreach ($properties as $index => &$property) {
                $property['team_member_id'] = $teamMembers[$index % $teamMembers->count()]->id;
            }
        }

        return $properties;
    }

    private function syntheticProperties(Agency $agency): array
    {
        $locations = [
            ['town' => 'Ranelagh', 'county' => 'Dublin', 'lat' => 53.326, 'lng' => -6.255],
            ['town' => 'Blackrock', 'county' => 'Dublin', 'lat' => 53.301, 'lng' => -6.177],
            ['town' => 'Malahide', 'county' => 'Dublin', 'lat' => 53.451, 'lng' => -6.154],
            ['town' => 'Bray', 'county' => 'Wicklow', 'lat' => 53.201, 'lng' => -6.111],
            ['town' => 'Naas', 'county' => 'Kildare', 'lat' => 53.220, 'lng' => -6.660],
            ['town' => 'Galway City', 'county' => 'Galway', 'lat' => 53.270, 'lng' => -9.056],
            ['town' => 'Limerick City', 'county' => 'Limerick', 'lat' => 52.663, 'lng' => -8.626],
            ['town' => 'Waterford City', 'county' => 'Waterford', 'lat' => 52.259, 'lng' => -7.110],
            ['town' => 'Cork City', 'county' => 'Cork', 'lat' => 51.898, 'lng' => -8.475],
            ['town' => 'Killarney', 'county' => 'Kerry', 'lat' => 52.059, 'lng' => -9.506],
            ['town' => 'Sligo Town', 'county' => 'Sligo', 'lat' => 54.276, 'lng' => -8.477],
            ['town' => 'Athlone', 'county' => 'Westmeath', 'lat' => 53.423, 'lng' => -7.940],
        ];

        $templates = [
            [
                'count' => 18,
                'transaction_type' => 'sale',
                'property_types' => ['house', 'apartment', 'detached', 'semi_detached', 'terraced', 'bungalow'],
                'title' => ':beds Bed :type for Sale in :town',
                'price_range' => [285000, 925000],
                'features' => ['Turnkey condition', 'Private garden', 'Close to transport', 'Energy efficient upgrades'],
            ],
            [
                'count' => 12,
                'transaction_type' => 'rent',
                'property_types' => ['apartment', 'house', 'studio', 'terraced'],
                'title' => ':beds Bed :type to Rent in :town',
                'price_range' => [1450, 3850],
                'features' => ['Furnished', 'Managed building', 'Excellent transport links', 'Available immediately'],
            ],
            [
                'count' => 10,
                'transaction_type' => 'commercial',
                'property_types' => ['office', 'retail', 'warehouse', 'restaurant', 'industrial'],
                'title' => ':type Opportunity in :town',
                'price_range' => [32000, 420000],
                'features' => ['High profile location', 'Flexible floorplate', 'Service access', 'Strong local footfall'],
            ],
            [
                'count' => 8,
                'transaction_type' => 'sale',
                'property_types' => ['site', 'land', 'parking', 'garage', 'farm'],
                'title' => ':type Listing in :town',
                'price_range' => [18000, 260000],
                'features' => ['Clear boundaries', 'Good road access', 'Rare local opportunity', 'Viewing by appointment'],
            ],
        ];

        $properties = [];
        $sequence = 1;

        foreach ($templates as $template) {
            for ($i = 0; $i < $template['count']; $i++) {
                $location = $locations[($sequence + $i) % count($locations)];
                $propertyType = $template['property_types'][$i % count($template['property_types'])];
                $bedrooms = in_array($propertyType, ['studio', 'office', 'retail', 'warehouse', 'restaurant', 'industrial', 'site', 'land', 'parking', 'garage', 'farm'], true)
                    ? null
                    : (($i % 5) + 1);
                $bathrooms = $bedrooms ? max(1, min(3, (int) ceil($bedrooms / 2))) : null;
                $title = strtr($template['title'], [
                    ':beds' => (string) ($bedrooms ?: 0),
                    ':type' => Str::headline($propertyType),
                    ':town' => $location['town'],
                ]);
                $publicId = 'prop_demo_extra_'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
                $price = $template['price_range'][0] + (($i * 37000) % ($template['price_range'][1] - $template['price_range'][0]));

                $properties[] = [
                    'agency_id' => $agency->id,
                    'public_id' => $publicId,
                    'title' => $title,
                    'slug' => Str::slug($title).'-'.$publicId,
                    'status' => ['available', 'available', 'available', 'under_offer', 'sale_agreed'][$i % 5],
                    'transaction_type' => $template['transaction_type'],
                    'property_type' => $propertyType,
                    'price' => $price,
                    'price_qualifier' => $template['transaction_type'] === 'rent' ? 'asking_price' : ['asking_price', 'guide_price', 'poa'][$i % 3],
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bathrooms,
                    'floor_area_m2' => $propertyType === 'parking' ? null : 45 + (($i * 17) % 240),
                    'ber_rating' => ['A0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'Exempt'][$i % 9],
                    'town' => $location['town'],
                    'county' => $location['county'],
                    'latitude' => $location['lat'] + ($i * 0.002),
                    'longitude' => $location['lng'] - ($i * 0.002),
                    'description' => "Synthetic demo listing for {$location['town']} with realistic local market details for layout, filtering, and multilingual testing.",
                    'features' => $template['features'],
                    'facilities' => $this->demoFacilities($propertyType, $i),
                    'online_offers_enabled' => $template['transaction_type'] === 'sale' && ! in_array($propertyType, ['site', 'land', 'parking', 'garage', 'farm'], true),
                    'published_at' => now()->subDays(($sequence % 28) + 1),
                ];

                $sequence++;
            }
        }

        return $properties;
    }

    private function demoFacilities(string $propertyType, int $index): array
    {
        $base = match ($propertyType) {
            'apartment', 'studio' => ['lift', 'balcony', 'furnished'],
            'office', 'retail', 'warehouse', 'industrial', 'restaurant' => ['parking', 'wheelchair_access', 'alarm'],
            'site', 'land', 'farm' => ['parking'],
            'parking', 'garage' => ['parking', 'ev_charging'],
            default => ['parking', 'garden', 'gas_heating'],
        };

        if ($index % 4 === 0) {
            $base[] = 'ev_charging';
        }

        if ($index % 5 === 0) {
            $base[] = 'pet_friendly';
        }

        return array_values(array_unique($base));
    }

    private function ensureDemoImage(Property $property, int $sortOrder = 1): string
    {
        $path = 'properties/demo/v2/'.$property->public_id.'-'.$sortOrder.'.jpg';

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        Storage::disk('public')->makeDirectory('properties/demo/v2');
        $absolutePath = Storage::disk('public')->path($path);
        $image = imagecreatetruecolor(1280, 820);
        $scenes = [
            ['Front exterior', [203, 213, 225], [20, 83, 45], [146, 64, 14]],
            ['Kitchen', [241, 245, 249], [15, 118, 110], [180, 83, 9]],
            ['Living room', [226, 232, 240], [51, 65, 85], [194, 120, 3]],
            ['Bedroom', [238, 242, 255], [67, 56, 202], [120, 53, 15]],
            ['Bathroom', [224, 242, 254], [14, 116, 144], [71, 85, 105]],
            ['Garden', [220, 252, 231], [22, 101, 52], [132, 204, 22]],
            ['Dining area', [250, 245, 235], [124, 45, 18], [217, 119, 6]],
            ['Home office', [241, 245, 249], [30, 41, 59], [37, 99, 235]],
            ['Hallway', [229, 231, 235], [75, 85, 99], [15, 118, 110]],
            ['Terrace', [224, 242, 254], [2, 132, 199], [202, 138, 4]],
            ['Parking', [226, 232, 240], [55, 65, 81], [5, 150, 105]],
            ['Neighbourhood', [221, 234, 244], [30, 64, 175], [4, 120, 87]],
        ];
        $scene = $scenes[($sortOrder - 1) % count($scenes)];
        [$sceneName, $backgroundRgb, $accentRgb, $warmRgb] = $scene;

        $background = imagecolorallocate($image, ...$backgroundRgb);
        $accent = imagecolorallocate($image, ...$accentRgb);
        $warm = imagecolorallocate($image, ...$warmRgb);
        $ink = imagecolorallocate($image, 15, 23, 42);
        $muted = imagecolorallocate($image, 100, 116, 139);
        $glass = imagecolorallocate($image, 248, 250, 252);
        $white = imagecolorallocate($image, 255, 255, 255);
        $shadow = imagecolorallocate($image, 30, 41, 59);

        imagefill($image, 0, 0, $background);
        imagefilledrectangle($image, 0, 620, 1280, 820, $accent);

        if (in_array($sceneName, ['Front exterior', 'Garden', 'Terrace', 'Parking', 'Neighbourhood'], true)) {
            imagefilledrectangle($image, 0, 0, 1280, 360, imagecolorallocate($image, 191, 219, 254));
            imagefilledrectangle($image, 0, 420, 1280, 620, imagecolorallocate($image, 187, 247, 208));
            imagefilledpolygon($image, [210, 330, 620, 130, 1030, 330], 3, $warm);
            imagefilledrectangle($image, 285, 330, 955, 620, $glass);
            imagefilledrectangle($image, 365, 405, 535, 620, imagecolorallocate($image, 96, 165, 250));
            imagefilledrectangle($image, 710, 398, 875, 620, imagecolorallocate($image, 125, 211, 252));
            imagefilledrectangle($image, 585, 470, 680, 620, $shadow);
        } else {
            imagefilledrectangle($image, 0, 0, 1280, 620, imagecolorallocate($image, 226, 232, 240));
            imagefilledrectangle($image, 70, 70, 1210, 620, imagecolorallocate($image, 248, 250, 252));
            imagefilledrectangle($image, 120, 160, 1160, 600, imagecolorallocate($image, 241, 245, 249));
            imagefilledrectangle($image, 170, 230, 600, 560, $glass);
            imagefilledrectangle($image, 665, 260, 1085, 560, imagecolorallocate($image, 203, 213, 225));
            imagefilledellipse($image, 385, 550, 420, 90, $warm);
            imagefilledrectangle($image, 725, 320, 1035, 520, $accent);
        }

        imagefilledrectangle($image, 70, 645, 1210, 780, imagecolorallocate($image, 248, 250, 252));
        imagestring($image, 5, 105, 675, $sceneName, $ink);
        imagestring($image, 4, 105, 710, substr($property->title, 0, 68), $muted);
        imagestring($image, 4, 105, 740, collect([$property->town, $property->county])->filter()->join(', '), $muted);
        imagestring($image, 4, 1030, 720, 'Demo '.$sortOrder.'/12', $accent);

        imagejpeg($image, $absolutePath, 86);
        imagedestroy($image);

        return $path;
    }
}
