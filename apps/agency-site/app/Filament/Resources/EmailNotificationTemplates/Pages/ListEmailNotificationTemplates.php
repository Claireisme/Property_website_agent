<?php

namespace App\Filament\Resources\EmailNotificationTemplates\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailNotificationTemplates\EmailNotificationTemplateResource;
use App\Support\EmailNotificationCatalog;
use Filament\Resources\Pages\ListRecords;

class ListEmailNotificationTemplates extends ListRecords
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailNotificationTemplateResource::class;

    protected function beforeFill(): void
    {
        EmailNotificationCatalog::syncDefaults();
    }

    public function mount(): void
    {
        EmailNotificationCatalog::syncDefaults();

        parent::mount();
    }
}
