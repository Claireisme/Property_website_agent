<?php

namespace App\Models;

use App\Services\PropertyImageVariantGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'original_url',
        'thumbnail_url',
        'card_url',
        'detail_url',
        'large_url',
        'caption',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saved(function (PropertyImage $image): void {
            if ($image->wasRecentlyCreated || $image->wasChanged('original_url')) {
                app(PropertyImageVariantGenerator::class)->generate($image);
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if ($this->isAbsoluteUrl($path) || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    public function canonicalUrl(?string $path): ?string
    {
        $url = $this->publicUrl($path);

        if (blank($url)) {
            return null;
        }

        $brandBaseUrl = rtrim((string) config('services.media_api.brand_base_url'), '/');
        $canonicalBaseUrl = rtrim((string) config('services.media_api.canonical_base_url'), '/');
        $tenant = trim((string) config('services.media_api.tenant'), '/');

        if (
            filled($brandBaseUrl)
            && filled($canonicalBaseUrl)
            && filled($tenant)
            && str_starts_with($url, $brandBaseUrl.'/')
        ) {
            return $canonicalBaseUrl.'/'.$tenant.'/'.ltrim(substr($url, strlen($brandBaseUrl)), '/');
        }

        return $url;
    }

    private function isAbsoluteUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }
}
