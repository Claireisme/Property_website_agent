<?php

namespace App\Services;

use App\Models\Property;
use App\Support\Locales;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeepSeekTranslationService
{
    public function translateProperty(Property $property, string $locale): array
    {
        $features = Property::normalizeFeatureList($property->features);

        if (filled(config('services.translation_gateway.url'))) {
            return $this->translateViaMainPortalGateway($property, $locale, $features);
        }

        $apiKey = config('services.deepseek.key');

        if (blank($apiKey)) {
            throw new RuntimeException('DEEPSEEK_API_KEY is not configured.');
        }

        $response = Http::withToken($apiKey)
            ->baseUrl(rtrim((string) config('services.deepseek.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(90)
            ->post('/chat/completions', [
                'model' => config('services.deepseek.model', 'deepseek-chat'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You translate Irish real-estate listing content. Preserve facts, measurements, addresses, BER ratings, prices, legal meaning, line breaks, and simple Markdown formatting such as bold or lists. Do not introduce HTML, colours, fonts, or inline styles. Return only valid JSON.',
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
                                'title' => $property->title,
                                'description' => $property->description,
                                'features' => $features,
                                'viewing_notes' => $property->viewing_notes,
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
            'title' => (string) ($decoded['title'] ?? $property->title),
            'description' => $decoded['description'] ?? $property->description,
            'features' => array_values($decoded['features'] ?? $features),
            'viewing_notes' => $decoded['viewing_notes'] ?? $property->viewing_notes,
        ];
    }

    private function translateViaMainPortalGateway(Property $property, string $locale, array $features): array
    {
        $token = config('services.translation_gateway.token');

        if (blank($token)) {
            throw new RuntimeException('TRANSLATION_GATEWAY_TOKEN is required when TRANSLATION_GATEWAY_URL is configured.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout(90)
            ->post(config('services.translation_gateway.url'), [
                'locale' => $locale,
                'source' => [
                    'title' => $property->title,
                    'description' => $property->description,
                    'features' => $features,
                    'viewing_notes' => $property->viewing_notes,
                ],
            ])
            ->throw()
            ->json();

        $translation = $response['translation'] ?? null;

        if (! is_array($translation)) {
            throw new RuntimeException('Main portal translation gateway did not return a translation payload.');
        }

        return [
            'title' => (string) ($translation['title'] ?? $property->title),
            'description' => $translation['description'] ?? $property->description,
            'features' => array_values($translation['features'] ?? $features),
            'viewing_notes' => $translation['viewing_notes'] ?? $property->viewing_notes,
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
