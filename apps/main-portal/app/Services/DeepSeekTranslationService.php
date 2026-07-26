<?php

namespace App\Services;

use App\Models\TranslationProviderSetting;
use App\Support\Locales;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeepSeekTranslationService
{
    public function translatePropertyPayload(array $source, string $locale): array
    {
        $settings = TranslationProviderSetting::deepSeek();

        if (! $settings->is_enabled) {
            throw new RuntimeException('DeepSeek translation is disabled in main portal settings.');
        }

        $apiKey = $settings->resolvedApiKey();

        if (blank($apiKey)) {
            throw new RuntimeException('DeepSeek API key is not configured in main portal settings.');
        }

        $response = Http::withToken($apiKey)
            ->baseUrl(rtrim($settings->base_url, '/'))
            ->acceptJson()
            ->asJson()
            ->timeout($settings->timeout_seconds)
            ->post('/chat/completions', [
                'model' => $settings->model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You translate Irish real-estate listing content. Preserve facts, measurements, addresses, BER ratings, prices, and legal meaning. Return only valid JSON.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'target_language' => $this->targetLanguageName($locale),
                            'required_json_shape' => [
                                'title' => 'string',
                                'description' => 'string or null',
                                'features' => ['array of strings'],
                                'viewing_notes' => 'string or null',
                            ],
                            'source' => [
                                'title' => $source['title'] ?? null,
                                'description' => $source['description'] ?? null,
                                'features' => $source['features'] ?? [],
                                'viewing_notes' => $source['viewing_notes'] ?? null,
                            ],
                        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ],
                ],
            ])
            ->throw()
            ->json();

        $content = data_get($response, 'choices.0.message.content');

        if (! is_string($content) || blank($content)) {
            throw new RuntimeException('DeepSeek did not return translation content.');
        }

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return [
            'title' => (string) ($decoded['title'] ?? ($source['title'] ?? '')),
            'description' => $decoded['description'] ?? ($source['description'] ?? null),
            'features' => array_values($decoded['features'] ?? ($source['features'] ?? [])),
            'viewing_notes' => $decoded['viewing_notes'] ?? ($source['viewing_notes'] ?? null),
        ];
    }

    private function targetLanguageName(string $locale): string
    {
        return match ($locale) {
            'pl' => 'Polish',
            'ro' => 'Romanian',
            'fr' => 'French',
            'es' => 'Spanish',
            'pt' => 'Portuguese',
            'lt' => 'Lithuanian',
            'de' => 'German',
            'zh' => 'Simplified Chinese',
            default => Locales::label($locale),
        };
    }
}
