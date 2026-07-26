<?php

namespace App\Filament\Resources\ValuationRequests\Pages;

use App\Filament\Resources\ValuationRequests\ValuationRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewValuationRequest extends ViewRecord
{
    protected static string $resource = ValuationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
