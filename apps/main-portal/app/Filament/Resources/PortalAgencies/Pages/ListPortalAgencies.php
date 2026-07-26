<?php

namespace App\Filament\Resources\PortalAgencies\Pages;

use App\Filament\Resources\PortalAgencies\PortalAgencyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortalAgencies extends ListRecords
{
    protected static string $resource = PortalAgencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
