<?php

namespace App\Filament\Resources\EmailNotificationTemplates\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailNotificationTemplates\EmailNotificationTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailNotificationTemplate extends ViewRecord
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
