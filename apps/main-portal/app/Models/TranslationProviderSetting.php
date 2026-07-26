<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationProviderSetting extends Model
{
    protected $fillable = [
        'provider',
        'is_enabled',
        'api_key',
        'base_url',
        'model',
        'timeout_seconds',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'api_key' => 'encrypted',
            'timeout_seconds' => 'integer',
        ];
    }

    public static function deepSeek(): self
    {
        return self::query()->firstOrCreate(
            ['provider' => 'deepseek'],
            [
                'is_enabled' => false,
                'base_url' => 'https://api.deepseek.com',
                'model' => 'deepseek-chat',
                'timeout_seconds' => 90,
            ],
        );
    }

    public function resolvedApiKey(): ?string
    {
        return $this->api_key ?: config('services.deepseek.key');
    }
}
