<?php

namespace App\Filament\Resources\ValuationRequests\Pages;

use App\Filament\Resources\ValuationRequests\ValuationRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditValuationRequest extends EditRecord
{
    protected static string $resource = ValuationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
