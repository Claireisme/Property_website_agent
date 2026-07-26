<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalPropertyTranslation extends Model
{
    protected $fillable = [
        'portal_property_id',
        'locale',
        'status',
        'title',
        'description',
        'features',
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
        return $this->belongsTo(PortalProperty::class, 'portal_property_id');
    }
}
