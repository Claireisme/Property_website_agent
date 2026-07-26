<?php

namespace App\Filament\Resources\PortalProperties\Pages;

use App\Filament\Resources\PortalProperties\PortalPropertyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPortalProperty extends EditRecord
{
    protected static string $resource = PortalPropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
