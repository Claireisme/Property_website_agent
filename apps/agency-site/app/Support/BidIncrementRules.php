<?php

namespace App\Support;

use App\Models\Agency;
use App\Models\Offer;
use App\Models\Property;

class BidIncrementRules
{
    private const ACTIVE_OFFER_STATUSES = [
        'submitted',
        'pending_review',
        'valid',
        'request_more_info',
        'countered',
        'accepted_subject_to_contract',
    ];

    public static function defaults(): array
    {
        return [
            [
                'min_price' => 0,
                'max_price' => 300000,
                'increment_amount' => 500,
            ],
            [
                'min_price' => 300001,
                'max_price' => 1000000,
                'increment_amount' => 1000,
            ],
            [
                'min_price' => 1000001,
                'max_price' => null,
                'increment_amount' => 5000,
            ],
        ];
    }

    public static function normalize(?array $rules): array
    {
        $normalized = collect($rules ?? [])
            ->map(function (array $rule): ?array {
                $increment = self::nullableInteger($rule['increment_amount'] ?? null);

                if ($increment === null || $increment < 1) {
                    return null;
                }

                return [
                    'min_price' => max(0, self::nullableInteger($rule['min_price'] ?? null) ?? 0),
                    'max_price' => self::nullableInteger($rule['max_price'] ?? null),
                    'increment_amount' => $increment,
                ];
            })
            ->filter()
            ->sortBy([
                ['min_price', 'asc'],
                ['max_price', 'asc'],
            ])
            ->values()
            ->all();

        return $normalized === [] ? self::defaults() : $normalized;
    }

    public static function rulesFor(?Agency $agency): array
    {
        return self::normalize($agency?->bid_increment_rules);
    }

    public static function incrementForProperty(Property $property): int
    {
        $askingPrice = (int) ($property->price ?? 0);

        foreach (self::rulesFor($property->agency) as $rule) {
            $minPrice = (int) ($rule['min_price'] ?? 0);
            $maxPrice = $rule['max_price'] ?? null;

            if ($askingPrice < $minPrice) {
                continue;
            }

            if ($maxPrice !== null && $askingPrice > (int) $maxPrice) {
                continue;
            }

            return (int) $rule['increment_amount'];
        }

        return (int) collect(self::rulesFor($property->agency))->last()['increment_amount'];
    }

    public static function highestActiveOfferAmount(Property $property): ?int
    {
        $amount = Offer::query()
            ->where('property_id', $property->id)
            ->whereIn('status', self::ACTIVE_OFFER_STATUSES)
            ->max('amount');

        return $amount === null ? null : (int) $amount;
    }

    public static function currentBaseAmount(Property $property): int
    {
        return self::highestActiveOfferAmount($property) ?? (int) ($property->price ?? 0);
    }

    public static function nextOfferAmount(Property $property): int
    {
        $highestOfferAmount = self::highestActiveOfferAmount($property);

        if ($highestOfferAmount !== null) {
            return $highestOfferAmount + self::incrementForProperty($property);
        }

        return max(1, (int) ($property->price ?? 0));
    }

    public static function amountValidationMessage(Property $property, int $amount): ?string
    {
        $increment = self::incrementForProperty($property);
        $baseAmount = self::currentBaseAmount($property);
        $minimumAmount = self::nextOfferAmount($property);

        if ($amount < $minimumAmount) {
            return sprintf(
                'The offer amount must be at least EUR %s.',
                number_format($minimumAmount),
            );
        }

        if ($baseAmount > 0 && ($amount - $baseAmount) % $increment !== 0) {
            return sprintf(
                'The offer amount must increase in EUR %s steps from the current price.',
                number_format($increment),
            );
        }

        if ($baseAmount === 0 && $amount % $increment !== 0) {
            return sprintf(
                'The offer amount must be a multiple of EUR %s.',
                number_format($increment),
            );
        }

        return null;
    }

    private static function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
