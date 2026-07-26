<?php

namespace App\Filament\Resources\EmailDeliveryLogs;

use App\Filament\Resources\EmailDeliveryLogs\Pages\ListEmailDeliveryLogs;
use App\Filament\Resources\EmailDeliveryLogs\Pages\ViewEmailDeliveryLog;
use App\Filament\Resources\EmailDeliveryLogs\Schemas\EmailDeliveryLogInfolist;
use App\Filament\Resources\EmailDeliveryLogs\Tables\EmailDeliveryLogsTable;
use App\Models\EmailDeliveryLog;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailDeliveryLogResource extends Resource
{
    protected static ?string $model = EmailDeliveryLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Email logs';

    protected static ?int $navigationSort = 72;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function infolist(Schema $schema): Schema
    {
        return EmailDeliveryLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailDeliveryLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailDeliveryLogs::route('/'),
            'view' => ViewEmailDeliveryLog::route('/{record}'),
        ];
    }
}
