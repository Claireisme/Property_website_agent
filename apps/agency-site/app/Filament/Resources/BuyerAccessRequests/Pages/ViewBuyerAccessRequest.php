<?php

namespace App\Filament\Resources\BuyerAccessRequests\Pages;

use App\Filament\Resources\BuyerAccessRequests\BuyerAccessRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBuyerAccessRequest extends ViewRecord
{
    protected static string $resource = BuyerAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
