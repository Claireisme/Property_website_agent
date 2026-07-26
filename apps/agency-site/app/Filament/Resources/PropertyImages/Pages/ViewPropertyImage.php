<?php

namespace App\Filament\Resources\PropertyImages\Pages;

use App\Filament\Resources\PropertyImages\PropertyImageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPropertyImage extends ViewRecord
{
    protected static string $resource = PropertyImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
