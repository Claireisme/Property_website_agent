<?php

namespace App\Filament\Resources\BuyerAccessRequests\Pages;

use App\Filament\Resources\BuyerAccessRequests\BuyerAccessRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBuyerAccessRequest extends EditRecord
{
    protected static string $resource = BuyerAccessRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
