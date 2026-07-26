<?php

namespace App\Filament\Resources\EmailSettings\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailSettings\EmailSettingResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailSetting extends EditRecord
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
