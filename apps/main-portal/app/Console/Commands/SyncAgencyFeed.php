<?php

namespace App\Console\Commands;

use App\Models\PortalAgency;
use App\Models\PortalProperty;
use App\Models\SyncRun;
use App\Support\Locales;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

#[Signature('sync:agency-feed {agency_id? : Sync one agency id. If omitted, sync all active agencies.}')]
#[Description('Pull authorized agency JSON feeds and upsert portal listing copies')]
class SyncAgencyFeed extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = PortalAgency::query()
            ->when(
                $this->argument('agency_id'),
                fn ($query, $agencyId) => $query->whereKey($agencyId),
                fn ($query) => $query->where('status', 'active'),
            );

        $agencies = $query->get();

        if ($agencies->isEmpty()) {
            $this->warn('No agencies to sync.');

            return self::SUCCESS;
        }

        $exitCode = self::SUCCESS;

        foreach ($agencies as $agency) {
            $this->line("Syncing {$agency->name}...");

            try {
                $this->syncAgency($agency);
                $this->info("Synced {$agency->name}.");
            } catch (Throwable $exception) {
                $exitCode = self::FAILURE;
                $this->error("Failed syncing {$agency->name}: {$exception->getMessage()}");
            }
        }

        return $exitCode;
    }

    private function syncAgency(PortalAgency $agency): void
    {
        $syncRun = SyncRun::query()->create([
            'portal_agency_id' => $agency->id,
            'status' => 'success',
            'started_at' => now(),
        ]);

        $seenExternalIds = [];
        $created = 0;
        $updated = 0;

        try {
            $nextUrl = $agency->feed_url;

            while ($nextUrl) {
                $payload = Http::withToken($agency->api_token_encrypted)
                    ->acceptJson()
                    ->timeout(30)
                    ->get($nextUrl)
                    ->throw()
                    ->json();

                foreach ($payload['properties'] ?? [] as $listing) {
                    $result = $this->upsertListing($agency, $listing);
                    $seenExternalIds[] = $listing['external_listing_id'];

                    if ($result === 'created') {
                        $created++;
                    } elseif ($result === 'updated') {
                        $updated++;
                    }
                }

                $nextUrl = $payload['pagination']['next_page_url'] ?? null;
            }

            $removed = PortalProperty::query()
                ->where('portal_agency_id', $agency->id)
                ->when(
                    $seenExternalIds !== [],
                    fn ($query) => $query->whereNotIn('external_listing_id', $seenExternalIds),
                )
                ->whereNotIn('status', ['withdrawn', 'archived'])
                ->update([
                    'status' => 'withdrawn',
                    'last_synced_at' => now(),
                ]);

            $syncRun->update([
                'status' => 'success',
                'finished_at' => now(),
                'listings_seen' => count(array_unique($seenExternalIds)),
                'listings_created' => $created,
                'listings_updated' => $updated,
                'listings_removed' => $removed,
            ]);

            $agency->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
                'last_error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $syncRun->update([
                'status' => 'failed',
                'finished_at' => now(),
                'listings_seen' => count(array_unique($seenExternalIds)),
                'listings_created' => $created,
                'listings_updated' => $updated,
                'error_message' => $exception->getMessage(),
            ]);

            $agency->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'failed',
                'last_error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function upsertListing(PortalAgency $agency, array $listing): string
    {
        $externalListingId = $listing['external_listing_id'];
        $existing = PortalProperty::query()
            ->where('portal_agency_id', $agency->id)
            ->where('external_listing_id', $externalListingId)
            ->first();

        $address = $listing['address'] ?? [];
        $sourceUpdatedAt = $this->parseDate($listing['updated_at'] ?? null);

        $values = [
            'portal_agency_id' => $agency->id,
            'external_listing_id' => $externalListingId,
            'source_url' => $listing['source_url'] ?? null,
            'title' => $listing['title'],
            'slug' => $existing?->slug ?: $this->makeSlug($listing['title'], $externalListingId),
            'status' => $listing['status'],
            'transaction_type' => $listing['transaction_type'] ?? null,
            'property_type' => $listing['property_type'] ?? null,
            'price' => $listing['price'] ?? null,
            'bedrooms' => $listing['bedrooms'] ?? null,
            'bathrooms' => $listing['bathrooms'] ?? null,
            'floor_area_m2' => $listing['floor_area_m2'] ?? null,
            'ber_rating' => $listing['ber_rating'] ?? null,
            'address_summary' => $address['summary'] ?? null,
            'town' => $address['town'] ?? null,
            'county' => $address['county'] ?? null,
            'eircode_hash' => filled($address['eircode'] ?? null) ? hash('sha256', $address['eircode']) : null,
            'latitude' => $address['latitude'] ?? null,
            'longitude' => $address['longitude'] ?? null,
            'description' => $listing['description'] ?? null,
            'images' => $listing['images'] ?? [],
            'features' => PortalProperty::normalizeFeatureList($listing['features'] ?? []),
            'facilities' => PortalProperty::normalizeFeatureList($listing['facilities'] ?? []),
            'online_offers_enabled' => (bool) ($listing['online_offers_enabled'] ?? false),
            'source_updated_at' => $sourceUpdatedAt,
            'first_synced_at' => $existing?->first_synced_at ?: now(),
            'last_synced_at' => now(),
        ];

        if (! $existing) {
            $property = PortalProperty::query()->create($values);
            $this->syncTranslations($property, $listing['translations'] ?? []);

            return 'created';
        }

        $existing->fill($values);

        if (! $existing->isDirty()) {
            $existing->forceFill(['last_synced_at' => now()])->save();
            $this->syncTranslations($existing, $listing['translations'] ?? []);

            return 'unchanged';
        }

        $existing->save();
        $this->syncTranslations($existing, $listing['translations'] ?? []);

        return 'updated';
    }

    private function syncTranslations(PortalProperty $property, array $translations): void
    {
        $seenLocales = [];

        foreach ($translations as $locale => $translation) {
            if (! is_string($locale) || ! Locales::isSupported($locale) || $locale === Locales::default()) {
                continue;
            }

            if (! is_array($translation) || blank($translation['title'] ?? null)) {
                continue;
            }

            $seenLocales[] = $locale;

            $property->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'status' => $translation['status'] ?? 'machine_translated',
                    'title' => $translation['title'],
                    'description' => $translation['description'] ?? null,
                    'features' => PortalProperty::normalizeFeatureList($translation['features'] ?? []),
                    'source_hash' => $translation['source_hash'] ?? hash('sha256', $translation['title']),
                    'error_message' => null,
                    'translated_at' => $this->parseDate($translation['translated_at'] ?? null),
                ],
            );
        }

        $property->translations()
            ->when(
                $seenLocales === [],
                fn ($query) => $query,
                fn ($query) => $query->whereNotIn('locale', $seenLocales),
            )
            ->delete();
    }

    private function makeSlug(string $title, string $externalListingId): string
    {
        return Str::slug($title).'-'.Str::slug($externalListingId);
    }

    private function parseDate(?string $value): ?Carbon
    {
        return filled($value) ? Carbon::parse($value) : null;
    }
}
