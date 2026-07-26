<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailDeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_key',
        'recipient_email',
        'recipient_name',
        'subject',
        'status',
        'error',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
