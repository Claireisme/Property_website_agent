<?php

namespace App\Filament\Resources\Properties\Pages\Concerns;

use Illuminate\Support\Arr;

trait SavesBulkPropertyPhotos
{
    protected function removeBulkPhotoUploadsFromPropertyData(array $data): array
    {
        unset($data['bulk_photo_uploads'], $data['floorplan_uploads']);

        return $data;
    }

    protected function saveBulkPropertyPhotos(): void
    {
        if (! $this->record) {
            return;
        }

        $paths = collect(Arr::wrap(data_get($this->data, 'bulk_photo_uploads')))
            ->filter(fn (mixed $path): bool => is_string($path) && filled($path))
            ->values();
        $floorplans = collect(Arr::wrap(data_get($this->data, 'floorplan_uploads')))
            ->filter(fn (mixed $path): bool => is_string($path) && filled($path))
            ->values();

        if ($paths->isEmpty() && $floorplans->isEmpty()) {
            return;
        }

        $nextSortOrder = ((int) $this->record->images()->max('sort_order')) + 1;

        foreach ($paths as $path) {
            $this->record->images()->create([
                'original_url' => $path,
                'sort_order' => $nextSortOrder++,
            ]);
        }

        foreach ($floorplans as $path) {
            $this->record->images()->create([
                'original_url' => $path,
                'caption' => 'Floor plan',
                'sort_order' => $nextSortOrder++,
            ]);
        }

        data_set($this->data, 'bulk_photo_uploads', []);
        data_set($this->data, 'floorplan_uploads', []);
        $this->record->unsetRelation('images');
    }
}
