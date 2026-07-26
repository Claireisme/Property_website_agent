<?php

namespace App\Filament\Resources\BuyerAccessRequests\Pages;

use App\Filament\Resources\BuyerAccessRequests\BuyerAccessRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBuyerAccessRequests extends ListRecords
{
    protected static string $resource = BuyerAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
