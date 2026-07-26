<?php

namespace App\Filament\Resources\PortalAgencies\Pages;

use App\Filament\Resources\PortalAgencies\PortalAgencyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPortalAgency extends ViewRecord
{
    protected static string $resource = PortalAgencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
