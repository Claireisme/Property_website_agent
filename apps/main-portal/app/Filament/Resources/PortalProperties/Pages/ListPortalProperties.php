<?php

namespace App\Filament\Resources\PortalProperties\Pages;

use App\Filament\Resources\PortalProperties\PortalPropertyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortalProperties extends ListRecords
{
    protected static string $resource = PortalPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
