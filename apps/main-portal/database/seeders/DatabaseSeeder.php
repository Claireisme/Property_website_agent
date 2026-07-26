<?php

namespace Database\Seeders;

use App\Models\PortalAgency;
use App\Models\TranslationProviderSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        PortalAgency::query()->updateOrCreate(
            ['name' => 'Example Estate Agents'],
            [
                'website_url' => 'http://127.0.0.1:8000',
                'feed_url' => 'http://127.0.0.1:8000/api/feed/v1/properties',
                'api_token_encrypted' => 'dev-feed-token',
                'status' => 'active',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Portal Admin',
                'password' => Hash::make('password'),
            ],
        );

        TranslationProviderSetting::query()->firstOrCreate(
            ['provider' => 'deepseek'],
            [
                'is_enabled' => false,
                'base_url' => 'https://api.deepseek.com',
                'model' => 'deepseek-chat',
                'timeout_seconds' => 90,
            ],
        );
    }
}
