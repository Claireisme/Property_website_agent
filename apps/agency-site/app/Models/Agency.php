<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trading_name',
        'company_registration_number',
        'psra_licence_number',
        'website_domain',
        'logo_path',
        'hero_image_path',
        'primary_colour',
        'secondary_colour',
        'phone',
        'email',
        'address',
        'county',
        'eircode',
        'description',
        'theme_key',
        'bid_increment_rules',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'tiktok_url',
        'linkedin_url',
        'x_url',
    ];

    protected function casts(): array
    {
        return [
            'bid_increment_rules' => 'array',
        ];
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
