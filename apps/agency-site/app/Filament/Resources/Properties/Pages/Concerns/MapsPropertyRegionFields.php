<?php

namespace App\Filament\Resources\Properties\Pages\Concerns;

use App\Support\LocationOptions;

trait MapsPropertyRegionFields
{
    protected function mapRegionOntoPropertyData(array $data): array
    {
        if (! array_key_exists('region', $data)) {
            return $data;
        }

        $location = LocationOptions::propertyFieldsForRegion($data['region'], $data['locality'] ?? null);

        $data['county'] = $location['county'];
        $data['town'] = $location['town'];

        unset($data['region'], $data['locality']);

        return $data;
    }
}
