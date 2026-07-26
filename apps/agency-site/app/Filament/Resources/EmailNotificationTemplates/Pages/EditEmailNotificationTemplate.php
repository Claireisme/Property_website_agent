<?php

namespace App\Filament\Resources\EmailNotificationTemplates\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailNotificationTemplates\EmailNotificationTemplateResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEmailNotificationTemplate extends EditRecord
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
