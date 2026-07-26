<?php

namespace App\Filament\Resources\OfferEvents\Pages;

use App\Filament\Resources\OfferEvents\OfferEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOfferEvent extends EditRecord
{
    protected static string $resource = OfferEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
