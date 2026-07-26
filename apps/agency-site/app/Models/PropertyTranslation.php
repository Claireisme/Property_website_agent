<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyTranslation extends Model
{
    protected $fillable = [
        'property_id',
        'locale',
        'status',
        'title',
        'description',
        'features',
        'viewing_notes',
        'source_hash',
        'error_message',
        'translated_at',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'translated_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public static function sourceHashFor(Property $property): string
    {
        return hash('sha256', json_encode([
            'title' => $property->title,
            'description' => $property->description,
            'features' => Property::normalizeFeatureList($property->features),
            'viewing_notes' => $property->viewing_notes,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
