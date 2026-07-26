<?php

namespace App\Filament\Resources\ValuationRequests\Pages;

use App\Filament\Resources\ValuationRequests\ValuationRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListValuationRequests extends ListRecords
{
    protected static string $resource = ValuationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
