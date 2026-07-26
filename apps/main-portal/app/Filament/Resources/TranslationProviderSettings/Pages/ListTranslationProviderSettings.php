<?php

namespace App\Filament\Resources\TranslationProviderSettings\Pages;

use App\Filament\Resources\TranslationProviderSettings\TranslationProviderSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTranslationProviderSettings extends ListRecords
{
    protected static string $resource = TranslationProviderSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
