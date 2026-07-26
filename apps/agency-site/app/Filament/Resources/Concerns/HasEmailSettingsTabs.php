<?php

namespace App\Filament\Resources\Concerns;

use App\Filament\Resources\EmailDeliveryLogs\EmailDeliveryLogResource;
use App\Filament\Resources\EmailNotificationTemplates\EmailNotificationTemplateResource;
use App\Filament\Resources\EmailSettings\EmailSettingResource;
use App\Models\EmailSetting;
use Filament\Navigation\NavigationItem;

use function Filament\Support\original_request;

trait HasEmailSettingsTabs
{
    /**
     * @return array<NavigationItem>
     */
    public function getSubNavigation(): array
    {
        return [
            NavigationItem::make('Setting')
                ->url(EmailSettingResource::getUrl('edit', ['record' => EmailSetting::current()], false))
                ->isActiveWhen(fn (): bool => original_request()->routeIs(EmailSettingResource::getRouteBaseName().'.*'))
                ->sort(1),
            NavigationItem::make('Templates')
                ->url(EmailNotificationTemplateResource::getUrl('index', [], false))
                ->isActiveWhen(fn (): bool => original_request()->routeIs(EmailNotificationTemplateResource::getRouteBaseName().'.*'))
                ->sort(2),
            NavigationItem::make('Logs')
                ->url(EmailDeliveryLogResource::getUrl('index', [], false))
                ->isActiveWhen(fn (): bool => original_request()->routeIs(EmailDeliveryLogResource::getRouteBaseName().'.*'))
                ->sort(3),
        ];
    }
}
