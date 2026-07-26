<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalAgency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'website_url',
        'feed_url',
        'api_token_encrypted',
        'status',
        'last_synced_at',
        'last_sync_status',
        'last_error_message',
    ];

    protected function casts(): array
    {
        return [
            'api_token_encrypted' => 'encrypted',
            'last_synced_at' => 'datetime',
        ];
    }

    public function properties(): HasMany
    {
        return $this->hasMany(PortalProperty::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(PortalEnquiry::class);
    }

    public function syncRuns(): HasMany
    {
        return $this->hasMany(SyncRun::class);
    }
}
