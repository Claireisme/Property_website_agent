<?php

namespace App\Models;

use App\Jobs\TranslatePropertyLocale;
use App\Support\Locales;
use App\Support\PropertyDescriptionFormatter;
use App\Support\PropertyOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory;

    private const INACTIVE_TRANSLATION_STATUSES = ['draft', 'archived'];

    private const TRANSLATION_SOURCE_ATTRIBUTES = [
        'title',
        'description',
        'features',
        'viewing_notes',
    ];

    protected $fillable = [
        'agency_id',
        'team_member_id',
        'public_id',
        'title',
        'slug',
        'status',
        'transaction_type',
        'property_type',
        'price',
        'price_qualifier',
        'bedrooms',
        'bathrooms',
        'floor_area_m2',
        'ber_rating',
        'address_line_1',
        'address_line_2',
        'town',
        'county',
        'eircode',
        'latitude',
        'longitude',
        'description',
        'features',
        'facilities',
        'viewing_notes',
        'online_offers_enabled',
        'published_at',
        'sale_agreed_at',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'facilities' => 'array',
            'online_offers_enabled' => 'boolean',
            'price' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'floor_area_m2' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'published_at' => 'datetime',
            'sale_agreed_at' => 'datetime',
            'sold_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Property $property): void {
            $teamMember = auth()->user()?->activeTeamMember();

            if (blank($property->team_member_id) && $teamMember) {
                $property->team_member_id = $teamMember->id;
            }

            if (blank($property->public_id)) {
                $property->public_id = 'prop_'.Str::lower((string) Str::ulid());
            }

            if (blank($property->slug)) {
                $baseSlug = Str::slug($property->title) ?: 'property';
                $property->slug = $baseSlug.'-'.Str::lower(Str::random(6));
            }
        });

        static::updating(function (Property $property): void {
            if ($property->isDirty('public_id')) {
                $property->public_id = $property->getOriginal('public_id');
            }

            if ($property->isDirty('slug')) {
                $property->slug = $property->getOriginal('slug');
            }
        });

        static::saved(function (Property $property): void {
            if (! $property->shouldQueueAutomaticTranslations()) {
                return;
            }

            $property->queueAutomaticTranslations();
        });
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function buyerAccessRequests(): HasMany
    {
        return $this->hasMany(BuyerAccessRequest::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PropertyTranslation::class);
    }

    public function translationFor(?string $locale = null): ?PropertyTranslation
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

    public function setDescriptionAttribute(?string $value): void
    {
        $this->attributes['description'] = PropertyDescriptionFormatter::cleanMarkdownInput($value);
    }

    public function setBerRatingAttribute(?string $value): void
    {
        $this->attributes['ber_rating'] = PropertyOptions::normalizeBerRating($value);
    }

    public function setFeaturesAttribute(mixed $value): void
    {
        $this->attributes['features'] = json_encode(self::normalizeFeatureList($value));
    }

    public function setFacilitiesAttribute(mixed $value): void
    {
        $this->attributes['facilities'] = json_encode(self::normalizeFeatureList($value));
    }

    public function localizedFeatures(?string $locale = null): array
    {
        return self::normalizeFeatureList($this->translationFor($locale)?->features ?: $this->features);
    }

    public function isTranslatableListing(): bool
    {
        return ! in_array($this->status, self::INACTIVE_TRANSLATION_STATUSES, true);
    }

    public function shouldQueueAutomaticTranslations(): bool
    {
        if (! self::automaticTranslationIsConfigured() || ! $this->isTranslatableListing()) {
            return false;
        }

        if ($this->wasRecentlyCreated || $this->wasChanged(self::TRANSLATION_SOURCE_ATTRIBUTES)) {
            return true;
        }

        if (! $this->wasChanged('status')) {
            return false;
        }

        return in_array((string) $this->getOriginal('status'), self::INACTIVE_TRANSLATION_STATUSES, true);
    }

    public function queueAutomaticTranslations(): void
    {
        $sourceHash = PropertyTranslation::sourceHashFor($this);

        foreach (Locales::nonDefaultCodes() as $locale) {
            TranslatePropertyLocale::dispatch($this->getKey(), $locale, $sourceHash);
        }
    }

    private static function automaticTranslationIsConfigured(): bool
    {
        return (bool) config('services.translation_gateway.auto_translate_properties', true)
            && (filled(config('services.translation_gateway.url'))
                || filled(config('services.deepseek.key')));
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->latest('id');
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

    public function listingCategory(): string
    {
        return PropertyOptions::categoryFor($this->transaction_type, $this->property_type);
    }

    public function scopeListingCategory(Builder $query, ?string $category): Builder
    {
        return match ($category) {
            'for_sale' => $query
                ->where('transaction_type', 'sale')
                ->where(function (Builder $query): void {
                    $query->whereNull('property_type')
                        ->orWhereNotIn('property_type', [
                            ...PropertyOptions::commercialPropertyTypes(),
                            ...PropertyOptions::otherPropertyTypes(),
                        ]);
                }),
            'to_rent' => $query
                ->where('transaction_type', 'rent')
                ->where(function (Builder $query): void {
                    $query->whereNull('property_type')
                        ->orWhereNotIn('property_type', [
                            ...PropertyOptions::commercialPropertyTypes(),
                            ...PropertyOptions::otherPropertyTypes(),
                        ]);
                }),
            'commercial' => $query->where(function (Builder $query): void {
                $query->where('transaction_type', 'commercial')
                    ->orWhereIn('property_type', PropertyOptions::commercialPropertyTypes());
            }),
            'other' => $query->whereIn('property_type', PropertyOptions::otherPropertyTypes()),
            default => $query,
        };
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
