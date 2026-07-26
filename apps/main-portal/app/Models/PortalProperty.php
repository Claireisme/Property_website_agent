<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PortalProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_agency_id',
        'external_listing_id',
        'source_url',
        'title',
        'slug',
        'status',
        'transaction_type',
        'property_type',
        'price',
        'bedrooms',
        'bathrooms',
        'floor_area_m2',
        'ber_rating',
        'address_summary',
        'town',
        'county',
        'eircode_hash',
        'latitude',
        'longitude',
        'description',
        'images',
        'features',
        'facilities',
        'online_offers_enabled',
        'source_updated_at',
        'first_synced_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'features' => 'array',
            'facilities' => 'array',
            'online_offers_enabled' => 'boolean',
            'price' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'floor_area_m2' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'source_updated_at' => 'datetime',
            'first_synced_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(PortalAgency::class, 'portal_agency_id');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(PortalEnquiry::class, 'portal_property_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PortalPropertyTranslation::class, 'portal_property_id');
    }

    public function translationFor(?string $locale = null): ?PortalPropertyTranslation
    {
        $locale ??= app()->getLocale();

        if ($locale === config('locales.default', 'en')) {
            return null;
        }

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('locale', $locale);
        }

        return $this->translations()->where('locale', $locale)->first();
    }

    public function localizedTitle(?string $locale = null): string
    {
        return $this->translationFor($locale)?->title ?: $this->title;
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        return $this->translationFor($locale)?->description ?: $this->description;
    }

    public function localizedFeatures(?string $locale = null): array
    {
        return self::normalizeFeatureList($this->translationFor($locale)?->features ?: $this->features);
    }

    public function setFeaturesAttribute(mixed $value): void
    {
        $this->attributes['features'] = json_encode(self::normalizeFeatureList($value));
    }

    public function setFacilitiesAttribute(mixed $value): void
    {
        $this->attributes['facilities'] = json_encode(self::normalizeFeatureList($value));
    }

    public static function normalizeFeatureList(mixed $features): array
    {
        if ($features instanceof Collection) {
            $features = $features->all();
        }

        if (blank($features)) {
            return [];
        }

        if (is_array($features)) {
            return collect($features)
                ->flatten()
                ->filter(fn (mixed $feature): bool => filled($feature))
                ->map(fn (mixed $feature): string => trim((string) $feature))
                ->filter()
                ->values()
                ->all();
        }

        if (is_string($features)) {
            $decoded = json_decode($features, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return self::normalizeFeatureList($decoded);
            }

            return collect(preg_split('/\r\n|\r|\n|,/', $features) ?: [])
                ->map(fn (string $feature): string => trim($feature))
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
