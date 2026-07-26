<?php

namespace App\Filament\Resources\PortalProperties\Pages;

use App\Filament\Resources\PortalProperties\PortalPropertyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPortalProperty extends ViewRecord
{
    protected static string $resource = PortalPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
