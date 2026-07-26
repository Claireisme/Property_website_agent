<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Services\DeepSeekTranslationService;
use App\Support\Locales;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('properties:translate {property_id? : Translate one property id. If omitted, translate all active properties.} {--locale=* : Locale code, repeatable. Defaults to all non-English locales.} {--force : Re-translate even when the source hash has not changed.} {--fake : Create clearly marked placeholder translations for local testing without DeepSeek.}')]
#[Description('Translate English property listing content into configured frontend languages')]
class TranslateProperties extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DeepSeekTranslationService $translator): int
    {
        $locales = $this->locales();

        if ($locales === []) {
            $this->error('No valid non-English locale was requested.');

            return self::FAILURE;
        }

        $properties = Property::query()
            ->when(
                $this->argument('property_id'),
                fn ($query, $propertyId) => $query->whereKey($propertyId),
                fn ($query) => $query->whereNotIn('status', ['draft', 'archived']),
            )
            ->with('translations')
            ->get();

        if ($properties->isEmpty()) {
            $this->warn('No properties matched the translation request.');

            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($properties as $property) {
            foreach ($locales as $locale) {
                if (! $this->translateProperty($property, $locale, $translator)) {
                    $failures++;
                }
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function translateProperty(Property $property, string $locale, DeepSeekTranslationService $translator): bool
    {
        $sourceHash = PropertyTranslation::sourceHashFor($property);
        $existing = $property->translations->firstWhere('locale', $locale);

        if (! $this->option('force') && $existing?->source_hash === $sourceHash) {
            $this->line("Skipped {$property->public_id} {$locale}; source is unchanged.");

            return true;
        }

        try {
            $payload = $this->option('fake')
                ? $this->fakeTranslation($property, $locale)
                : $translator->translateProperty($property, $locale);

            $property->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'status' => $this->option('fake') ? 'test_placeholder' : 'machine_translated',
                    'title' => $payload['title'],
                    'description' => $payload['description'],
                    'features' => $payload['features'],
                    'viewing_notes' => $payload['viewing_notes'],
                    'source_hash' => $sourceHash,
                    'error_message' => null,
                    'translated_at' => now(),
                ],
            );

            $this->info("Translated {$property->public_id} {$locale}.");

            return true;
        } catch (Throwable $exception) {
            $property->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'status' => 'failed',
                    'title' => $property->title,
                    'description' => $property->description,
                    'features' => Property::normalizeFeatureList($property->features),
                    'viewing_notes' => $property->viewing_notes,
                    'source_hash' => $sourceHash,
                    'error_message' => $exception->getMessage(),
                    'translated_at' => null,
                ],
            );

            $this->error("Failed {$property->public_id} {$locale}: {$exception->getMessage()}");

            return false;
        }
    }

    private function locales(): array
    {
        $requested = collect($this->option('locale'))
            ->flatMap(fn (string $locale): array => array_filter(array_map('trim', explode(',', $locale))))
            ->whenEmpty(fn ($locales) => $locales->merge(Locales::nonDefaultCodes()))
            ->unique()
            ->values();

        return $requested
            ->filter(fn (string $locale): bool => $locale !== Locales::default() && Locales::isSupported($locale))
            ->values()
            ->all();
    }

    private function fakeTranslation(Property $property, string $locale): array
    {
        $prefix = '['.strtoupper($locale).' test] ';

        return [
            'title' => $prefix.$property->title,
            'description' => $property->description ? $prefix.$property->description : null,
            'features' => collect(Property::normalizeFeatureList($property->features))
                ->map(fn (string $feature): string => $prefix.$feature)
                ->values()
                ->all(),
            'viewing_notes' => $property->viewing_notes ? $prefix.$property->viewing_notes : null,
        ];
    }
}
