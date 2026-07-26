<?php

namespace App\Filament\Resources\PropertyImages\Pages;

use App\Filament\Resources\PropertyImages\PropertyImageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPropertyImage extends EditRecord
{
    protected static string $resource = PropertyImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
        ];
    }
}
