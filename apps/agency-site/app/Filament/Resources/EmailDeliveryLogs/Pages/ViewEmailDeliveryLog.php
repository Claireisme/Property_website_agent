<?php

namespace App\Filament\Resources\EmailDeliveryLogs\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailDeliveryLogs\EmailDeliveryLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailDeliveryLog extends ViewRecord
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailDeliveryLogResource::class;
}
