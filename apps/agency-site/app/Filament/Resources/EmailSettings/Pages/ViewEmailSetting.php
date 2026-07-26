<?php

namespace App\Filament\Resources\EmailSettings\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailSettings\EmailSettingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailSetting extends ViewRecord
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
