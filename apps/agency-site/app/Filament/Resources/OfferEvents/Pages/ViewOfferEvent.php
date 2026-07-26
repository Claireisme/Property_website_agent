<?php

namespace App\Filament\Resources\OfferEvents\Pages;

use App\Filament\Resources\OfferEvents\OfferEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOfferEvent extends ViewRecord
{
    protected static string $resource = OfferEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
