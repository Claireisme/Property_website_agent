<?php

namespace App\Filament\Resources\EmailSettings\Pages;

use App\Filament\Resources\Concerns\HasEmailSettingsTabs;
use App\Filament\Resources\EmailSettings\EmailSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailSetting extends CreateRecord
{
    use HasEmailSettingsTabs;

    protected static string $resource = EmailSettingResource::class;
}
