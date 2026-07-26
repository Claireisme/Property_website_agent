<?php

namespace Tests\Feature;

use App\Models\TranslationProviderSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslationGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_gateway_requires_internal_token(): void
    {
        config(['services.translation_gateway.token' => 'internal-token']);

        $this->postJson('/api/internal/translations/property', [])
            ->assertUnauthorized();
    }

    public function test_translation_gateway_returns_deepseek_translation(): void
    {
        config(['services.translation_gateway.token' => 'internal-token']);

        TranslationProviderSetting::query()->create([
            'provider' => 'deepseek',
            'is_enabled' => true,
            'api_key' => 'deepseek-test-key',
            'base_url' => 'https://api.deepseek.test',
            'model' => 'deepseek-chat',
            'timeout_seconds' => 90,
        ]);

        Http::fake([
            'api.deepseek.test/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Translated title',
                                'description' => 'Translated description',
                                'features' => ['Translated feature'],
                                'viewing_notes' => null,
                            ]),
                        ],
                    ],
                ],
            ]),
        ]);

        $this->withToken('internal-token')
            ->postJson('/api/internal/translations/property', [
                'locale' => 'fr',
                'source' => [
                    'title' => 'English title',
                    'description' => 'English description',
                    'features' => ['English feature'],
                    'viewing_notes' => null,
                ],
            ])
            ->assertOk()
            ->assertJsonPath('provider', 'deepseek')
            ->assertJsonPath('translation.title', 'Translated title')
            ->assertJsonPath('translation.features.0', 'Translated feature');
    }
}
