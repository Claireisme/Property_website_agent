<?php

namespace App\Filament\Resources\OfferEvents\Pages;

use App\Filament\Resources\OfferEvents\OfferEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOfferEvents extends ListRecords
{
    protected static string $resource = OfferEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
