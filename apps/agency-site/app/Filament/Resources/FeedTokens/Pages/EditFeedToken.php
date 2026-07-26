<?php

namespace App\Filament\Resources\FeedTokens\Pages;

use App\Filament\Resources\FeedTokens\FeedTokenResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedToken extends EditRecord
{
    protected static string $resource = FeedTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
