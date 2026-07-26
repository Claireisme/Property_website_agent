<?php

namespace App\Jobs;

use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Services\DeepSeekTranslationService;
use App\Support\Locales;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class TranslatePropertyLocale implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function __construct(
        public int $propertyId,
        public string $locale,
        public string $sourceHash,
    ) {
        //
    }

    public function handle(DeepSeekTranslationService $translator): void
    {
        $property = Property::query()->find($this->propertyId);

        if (! $property || ! $property->isTranslatableListing()) {
            return;
        }

        if (! Locales::isSupported($this->locale) || $this->locale === Locales::default()) {
            return;
        }

        $currentSourceHash = PropertyTranslation::sourceHashFor($property);

        if ($currentSourceHash !== $this->sourceHash) {
            return;
        }

        $existing = $property->translations()
            ->where('locale', $this->locale)
            ->first();

        if ($existing?->status === 'machine_translated' && $existing->source_hash === $currentSourceHash) {
            return;
        }

        try {
            $payload = $translator->translateProperty($property, $this->locale);

            $property->translations()->updateOrCreate(
                ['locale' => $this->locale],
                [
                    'status' => 'machine_translated',
                    'title' => $payload['title'],
                    'description' => $payload['description'],
                    'features' => $payload['features'],
                    'viewing_notes' => $payload['viewing_notes'],
                    'source_hash' => $currentSourceHash,
                    'error_message' => null,
                    'translated_at' => now(),
                ],
            );
        } catch (Throwable $exception) {
            $property->translations()->updateOrCreate(
                ['locale' => $this->locale],
                [
                    'status' => 'failed',
                    'title' => $property->title,
                    'description' => $property->description,
                    'features' => Property::normalizeFeatureList($property->features),
                    'viewing_notes' => $property->viewing_notes,
                    'source_hash' => $currentSourceHash,
                    'error_message' => $exception->getMessage(),
                    'translated_at' => null,
                ],
            );

            throw $exception;
        }
    }
}
