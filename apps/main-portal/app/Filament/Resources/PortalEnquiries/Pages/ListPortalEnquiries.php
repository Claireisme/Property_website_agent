<?php

namespace App\Filament\Resources\PortalEnquiries\Pages;

use App\Filament\Resources\PortalEnquiries\PortalEnquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortalEnquiries extends ListRecords
{
    protected static string $resource = PortalEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
