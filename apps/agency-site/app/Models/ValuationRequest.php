<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValuationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'property_address',
        'eircode',
        'property_type',
        'bedrooms',
        'bathrooms',
        'preferred_contact_method',
        'selling_timeline',
        'message',
        'source',
        'status',
    ];
}
