<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\Pages\Concerns\MapsPropertyRegionFields;
use App\Filament\Resources\Properties\Pages\Concerns\SavesBulkPropertyPhotos;
use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\Properties\Schemas\PropertyForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateProperty extends CreateRecord
{
    use HasWizard;
    use MapsPropertyRegionFields;
    use SavesBulkPropertyPhotos;

    protected static string $resource = PropertyResource::class;

    public function getSteps(): array
    {
        return PropertyForm::steps();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->removeBulkPhotoUploadsFromPropertyData(
            $this->mapRegionOntoPropertyData($data),
        );
    }

    protected function afterCreate(): void
    {
        $this->saveBulkPropertyPhotos();
    }
}
