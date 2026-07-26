<?php

namespace App\Filament\Resources\PortalEnquiries\Pages;

use App\Filament\Resources\PortalEnquiries\PortalEnquiryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPortalEnquiry extends EditRecord
{
    protected static string $resource = PortalEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
