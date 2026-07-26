<?php

namespace App\Support;

class PropertyOptions
{
    public static function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'available' => 'Available',
            'under_offer' => 'Under offer',
            'sale_agreed' => 'Sale agreed',
            'sold' => 'Sold',
            'withdrawn' => 'Withdrawn',
            'archived' => 'Archived',
        ];
    }

    public static function publicStatuses(): array
    {
        return collect(self::statuses())
            ->except(['draft', 'archived'])
            ->all();
    }

    public static function transactionTypes(): array
    {
        return [
            'sale' => 'Sale',
            'rent' => 'Rent',
            'commercial' => 'Commercial',
        ];
    }

    public static function listingCategories(): array
    {
        return [
            'all' => 'All properties',
            'for_sale' => 'Residential for sale',
            'to_rent' => 'Residential to rent',
            'commercial' => 'Commercial property',
            'other' => 'Other',
        ];
    }

    public static function propertyTypes(): array
    {
        return [
            'house' => 'House',
            'apartment' => 'Apartment',
            'studio' => 'Studio',
            'bungalow' => 'Bungalow',
            'detached' => 'Detached',
            'semi_detached' => 'Semi-detached',
            'terraced' => 'Terraced',
            'site' => 'Site',
            'land' => 'Land',
            'parking' => 'Parking',
            'garage' => 'Garage',
            'farm' => 'Farm',
            'commercial' => 'Commercial',
            'office' => 'Office',
            'retail' => 'Retail',
            'industrial' => 'Industrial',
            'warehouse' => 'Warehouse',
            'restaurant' => 'Restaurant',
            'pub' => 'Pub',
        ];
    }

    public static function commercialPropertyTypes(): array
    {
        return [
            'commercial',
            'office',
            'retail',
            'industrial',
            'warehouse',
            'restaurant',
            'pub',
        ];
    }

    public static function otherPropertyTypes(): array
    {
        return [
            'site',
            'land',
            'parking',
            'garage',
            'farm',
        ];
    }

    public static function categoryFor(?string $transactionType, ?string $propertyType): string
    {
        if (in_array($propertyType, self::otherPropertyTypes(), true)) {
            return 'other';
        }

        if ($transactionType === 'commercial' || in_array($propertyType, self::commercialPropertyTypes(), true)) {
            return 'commercial';
        }

        if ($transactionType === 'rent') {
            return 'to_rent';
        }

        return 'for_sale';
    }

    public static function priceQualifiers(): array
    {
        return [
            'asking_price' => 'Asking price',
            'guide_price' => 'Guide price',
            'poa' => 'POA',
        ];
    }

    public static function berRatings(): array
    {
        return [
            'A0' => 'A0',
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
            'E' => 'E',
            'F' => 'F',
            'G' => 'G',
            'Exempt' => 'Exempt',
        ];
    }

    public static function normalizeBerRating(?string $rating): ?string
    {
        if (blank($rating)) {
            return null;
        }

        $rating = strtoupper(trim($rating));

        if ($rating === 'EXEMPT') {
            return 'Exempt';
        }

        if ($rating === 'A0') {
            return 'A0';
        }

        $band = substr($rating, 0, 1);

        return in_array($band, ['A', 'B', 'C', 'D', 'E', 'F', 'G'], true) ? $band : $rating;
    }

    public static function berAssetLevel(?string $rating): ?string
    {
        $rating = self::normalizeBerRating($rating);

        if ($rating === null || $rating === 'Exempt') {
            return null;
        }

        return strtolower($rating);
    }

    public static function minimumBerRatings(): array
    {
        return [
            'A0' => 'A0 only',
            'A' => 'A or better',
            'B' => 'B or better',
            'C' => 'C or better',
            'D' => 'D or better',
            'E' => 'E or better',
            'F' => 'F or better',
            'G' => 'G or better',
        ];
    }

    public static function berRatingsAtLeast(?string $minimum): array
    {
        return match ($minimum) {
            'A0' => ['A0'],
            'A' => ['A0', 'A', 'A1', 'A2', 'A3'],
            'B' => ['A0', 'A', 'A1', 'A2', 'A3', 'B', 'B1', 'B2', 'B3'],
            'C' => ['A0', 'A', 'A1', 'A2', 'A3', 'B', 'B1', 'B2', 'B3', 'C', 'C1', 'C2', 'C3'],
            'D' => ['A0', 'A', 'A1', 'A2', 'A3', 'B', 'B1', 'B2', 'B3', 'C', 'C1', 'C2', 'C3', 'D', 'D1', 'D2'],
            'E' => ['A0', 'A', 'A1', 'A2', 'A3', 'B', 'B1', 'B2', 'B3', 'C', 'C1', 'C2', 'C3', 'D', 'D1', 'D2', 'E', 'E1', 'E2'],
            'F' => ['A0', 'A', 'A1', 'A2', 'A3', 'B', 'B1', 'B2', 'B3', 'C', 'C1', 'C2', 'C3', 'D', 'D1', 'D2', 'E', 'E1', 'E2', 'F'],
            'G' => ['A0', 'A', 'A1', 'A2', 'A3', 'B', 'B1', 'B2', 'B3', 'C', 'C1', 'C2', 'C3', 'D', 'D1', 'D2', 'E', 'E1', 'E2', 'F', 'G'],
            default => [],
        };
    }

    public static function facilities(): array
    {
        return [
            'parking' => 'Parking',
            'garden' => 'Garden',
            'balcony' => 'Balcony',
            'lift' => 'Lift',
            'alarm' => 'Alarm',
            'gas_heating' => 'Gas heating',
            'oil_heating' => 'Oil heating',
            'wheelchair_access' => 'Wheelchair access',
            'furnished' => 'Furnished',
            'pet_friendly' => 'Pet friendly',
            'sea_view' => 'Sea view',
            'ev_charging' => 'EV charging',
        ];
    }

    public static function facilityFilters(): array
    {
        return [
            'parking' => 'Parking',
            'garden' => 'Garden',
            'balcony' => 'Balcony',
            'lift' => 'Lift',
            'alarm' => 'Alarm',
            'heating' => 'Heating',
            'furnished' => 'Furnished',
            'pet_friendly' => 'Pet friendly',
            'sea_view' => 'Sea view',
            'ev_charging' => 'EV charging',
        ];
    }

    public static function facilityFilterValues(string $facility): array
    {
        return match ($facility) {
            'heating' => ['heating', 'gas_heating', 'oil_heating'],
            default => array_key_exists($facility, self::facilityFilters()) ? [$facility] : [],
        };
    }

    public static function featureIcon(string $feature): string
    {
        $value = strtolower($feature);

        return match (true) {
            str_contains($value, 'parking') || str_contains($value, 'garage') => '🅿️',
            str_contains($value, 'garden') || str_contains($value, 'grounds') => '🌿',
            str_contains($value, 'balcony') || str_contains($value, 'terrace') => '☀️',
            str_contains($value, 'lift') => '↕️',
            str_contains($value, 'heating') || str_contains($value, 'ber') => '🔥',
            str_contains($value, 'sea') || str_contains($value, 'view') => '🌊',
            str_contains($value, 'transport') || str_contains($value, 'access') => '🚆',
            str_contains($value, 'furnished') => '🛋️',
            str_contains($value, 'alarm') || str_contains($value, 'secure') => '🔒',
            default => '✓',
        };
    }

    public static function listedWithinOptions(): array
    {
        return [
            '1' => 'Last 24 hours',
            '3' => 'Last 3 days',
            '7' => 'Last 7 days',
            '14' => 'Last 14 days',
            '30' => 'Last 30 days',
        ];
    }

    public static function sortOptions(): array
    {
        return [
            'newest' => 'Newest first',
            'price_asc' => 'Price low to high',
            'price_desc' => 'Price high to low',
            'beds_desc' => 'Most bedrooms',
            'area_desc' => 'Largest floor area',
        ];
    }

    public static function leadStatuses(): array
    {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'closed' => 'Closed',
            'spam' => 'Spam',
        ];
    }

    public static function enquiryTypes(): array
    {
        return [
            'question' => 'Question',
            'viewing' => 'Viewing',
            'callback' => 'Callback',
        ];
    }

    public static function offerStatuses(): array
    {
        return [
            'submitted' => 'Submitted',
            'pending_review' => 'Pending review',
            'valid' => 'Valid',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
            'request_more_info' => 'Request more info',
            'countered' => 'Countered',
            'accepted_subject_to_contract' => 'Accepted subject to contract',
            'superseded' => 'Superseded',
        ];
    }

    public static function buyerAccessStatuses(): array
    {
        return [
            'submitted' => 'Submitted',
            'pending_documents' => 'Pending documents',
            'pending_review' => 'Pending review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
        ];
    }

    public static function financingTypes(): array
    {
        return [
            'cash' => 'Cash',
            'mortgage' => 'Mortgage',
            'mixed' => 'Mixed',
        ];
    }

    public static function buyerPositions(): array
    {
        return [
            'first_time_buyer' => 'First-time buyer',
            'selling_own_property' => 'Selling own property',
            'renting' => 'Renting',
            'investor' => 'Investor',
        ];
    }
}
