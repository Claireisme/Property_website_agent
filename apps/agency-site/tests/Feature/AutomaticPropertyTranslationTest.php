<?php

namespace Tests\Feature;

use App\Jobs\TranslatePropertyLocale;
use App\Models\Agency;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Services\DeepSeekTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AutomaticPropertyTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_property_creation_queues_translation_jobs_when_gateway_is_configured(): void
    {
        config([
            'services.translation_gateway.url' => 'https://gateway.test/api/internal/translations/property',
            'services.translation_gateway.token' => 'internal-token',
            'services.translation_gateway.auto_translate_properties' => true,
        ]);

        Queue::fake();

        $agency = Agency::query()->create([
            'name' => 'Auto Translate Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_auto_translate',
            'title' => 'Auto Translate Property',
            'slug' => 'auto-translate-property',
            'status' => 'available',
            'description' => 'Bright home near transport.',
            'features' => ['Garden', 'Parking'],
            'published_at' => now(),
        ]);

        Queue::assertPushed(TranslatePropertyLocale::class, 8);
        Queue::assertPushed(
            TranslatePropertyLocale::class,
            fn (TranslatePropertyLocale $job): bool => $job->propertyId === $property->id
                && $job->locale === 'zh'
                && $job->sourceHash === PropertyTranslation::sourceHashFor($property),
        );
    }

    public function test_draft_property_queues_translation_jobs_when_it_is_published(): void
    {
        config([
            'services.translation_gateway.url' => 'https://gateway.test/api/internal/translations/property',
            'services.translation_gateway.token' => 'internal-token',
            'services.translation_gateway.auto_translate_properties' => true,
        ]);

        Queue::fake();

        $agency = Agency::query()->create([
            'name' => 'Draft Translate Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_draft_translate',
            'title' => 'Draft Translate Property',
            'slug' => 'draft-translate-property',
            'status' => 'draft',
            'description' => 'Draft description.',
            'features' => ['Garden'],
        ]);

        Queue::assertNothingPushed();

        $property->update([
            'status' => 'available',
            'published_at' => now(),
        ]);

        Queue::assertPushed(TranslatePropertyLocale::class, 8);
    }

    public function test_translation_job_uses_gateway_and_normalizes_string_features(): void
    {
        config([
            'services.translation_gateway.url' => 'https://gateway.test/api/internal/translations/property',
            'services.translation_gateway.token' => 'internal-token',
            'services.translation_gateway.auto_translate_properties' => true,
        ]);

        Http::fake([
            'https://gateway.test/*' => Http::response([
                'translation' => [
                    'title' => '自动翻译房源',
                    'description' => '已翻译描述。',
                    'features' => ['宽带', '中央供暖'],
                    'viewing_notes' => '预约看房。',
                ],
            ]),
        ]);

        $agency = Agency::query()->create([
            'name' => 'Job Translate Agency',
        ]);

        $property = Property::withoutEvents(fn (): Property => Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_job_translate',
            'title' => 'Job Translate Property',
            'slug' => 'job-translate-property',
            'status' => 'available',
            'description' => 'Original description.',
            'features' => ['Placeholder'],
            'viewing_notes' => 'View by appointment.',
            'published_at' => now(),
        ]));

        DB::table('properties')
            ->where('id', $property->id)
            ->update(['features' => json_encode('Broadband,Central Heating', JSON_THROW_ON_ERROR)]);

        $property = Property::query()->findOrFail($property->id);

        (new TranslatePropertyLocale(
            $property->id,
            'zh',
            PropertyTranslation::sourceHashFor($property),
        ))->handle(app(DeepSeekTranslationService::class));

        $this->assertDatabaseHas('property_translations', [
            'property_id' => $property->id,
            'locale' => 'zh',
            'status' => 'machine_translated',
            'title' => '自动翻译房源',
        ]);

        $this->assertSame(
            ['宽带', '中央供暖'],
            $property->translations()->where('locale', 'zh')->firstOrFail()->features,
        );

        $request = Http::recorded()->first()[0] ?? null;

        $this->assertNotNull($request);
        $this->assertSame('https://gateway.test/api/internal/translations/property', $request->url());
        $this->assertSame('Bearer internal-token', $request->header('Authorization')[0] ?? null);
        $this->assertSame('zh', $request['locale']);
        $this->assertSame(['Broadband', 'Central Heating'], $request['source']['features']);
    }
}
