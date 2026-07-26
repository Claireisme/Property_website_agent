<?php

namespace App\Models;

use App\Support\EmailNotificationCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'audience',
        'is_enabled',
        'subject',
        'body',
        'available_variables',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'available_variables' => 'array',
        ];
    }

    public static function forKey(string $key): self
    {
        $default = EmailNotificationCatalog::template($key);

        if ($default === null) {
            return self::query()->firstOrCreate([
                'key' => $key,
            ], [
                'label' => str($key)->replace('_', ' ')->headline()->toString(),
                'audience' => 'buyer',
                'subject' => '{{ site_name }} notification',
                'body' => 'A new notification is available.',
                'available_variables' => [],
            ]);
        }

        $template = self::query()->firstOrCreate([
            'key' => $key,
        ], [
            'label' => $default['label'],
            'audience' => $default['audience'],
            'subject' => $default['subject'],
            'body' => $default['body'],
            'available_variables' => $default['variables'],
        ]);

        $template->forceFill([
            'label' => $default['label'],
            'audience' => $default['audience'],
            'available_variables' => $default['variables'],
        ])->saveQuietly();

        return $template->refresh();
    }
}
