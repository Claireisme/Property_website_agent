<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\Pages\Concerns\MapsPropertyRegionFields;
use App\Filament\Resources\Properties\Pages\Concerns\SavesBulkPropertyPhotos;
use App\Filament\Resources\Properties\PropertyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    use MapsPropertyRegionFields;
    use SavesBulkPropertyPhotos;

    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['public_id'], $data['slug']);

        return $this->removeBulkPhotoUploadsFromPropertyData(
            $this->mapRegionOntoPropertyData($data),
        );
    }

    protected function afterSave(): void
    {
        $this->saveBulkPropertyPhotos();
    }
}
