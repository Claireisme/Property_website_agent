<?php

namespace App\Filament\Resources\EmailSettings;

use App\Filament\Resources\EmailDeliveryLogs\EmailDeliveryLogResource;
use App\Filament\Resources\EmailNotificationTemplates\EmailNotificationTemplateResource;
use App\Filament\Resources\EmailSettings\Pages\CreateEmailSetting;
use App\Filament\Resources\EmailSettings\Pages\EditEmailSetting;
use App\Filament\Resources\EmailSettings\Pages\ListEmailSettings;
use App\Filament\Resources\EmailSettings\Pages\ViewEmailSetting;
use App\Filament\Resources\EmailSettings\Schemas\EmailSettingForm;
use App\Filament\Resources\EmailSettings\Schemas\EmailSettingInfolist;
use App\Filament\Resources\EmailSettings\Tables\EmailSettingsTable;
use App\Models\EmailSetting;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailSettingResource extends Resource
{
    protected static ?string $model = EmailSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Email Settings';

    protected static ?int $navigationSort = 70;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationUrl(): string
    {
        return static::getUrl('edit', ['record' => EmailSetting::current()], false);
    }

    public static function getNavigationItemActiveRoutePattern(): array
    {
        return [
            static::getRouteBaseName().'.*',
            EmailNotificationTemplateResource::getRouteBaseName().'.*',
            EmailDeliveryLogResource::getRouteBaseName().'.*',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return EmailSettingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailSettingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailSettings::route('/'),
            'create' => CreateEmailSetting::route('/create'),
            'view' => ViewEmailSetting::route('/{record}'),
            'edit' => EditEmailSetting::route('/{record}/edit'),
        ];
    }
}
