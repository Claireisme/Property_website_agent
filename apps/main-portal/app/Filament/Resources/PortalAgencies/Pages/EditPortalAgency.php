<?php

namespace App\Filament\Resources\PortalAgencies\Pages;

use App\Filament\Resources\PortalAgencies\PortalAgencyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPortalAgency extends EditRecord
{
    protected static string $resource = PortalAgencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
