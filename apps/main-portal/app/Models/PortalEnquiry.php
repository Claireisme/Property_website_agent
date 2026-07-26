<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalEnquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'portal_agency_id',
        'portal_property_id',
        'name',
        'email',
        'phone',
        'message',
        'source',
        'status',
        'forwarded_at',
    ];

    protected function casts(): array
    {
        return [
            'forwarded_at' => 'datetime',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(PortalAgency::class, 'portal_agency_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(PortalProperty::class, 'portal_property_id');
    }
}
