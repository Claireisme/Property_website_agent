<?php

namespace App\Filament\Resources\FeedTokens\Pages;

use App\Filament\Resources\FeedTokens\FeedTokenResource;
use Filament\Resources\Pages\ViewRecord;

class ViewFeedToken extends ViewRecord
{
    protected static string $resource = FeedTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
