<?php

namespace App\Filament\Resources\EmailNotificationTemplates;

use App\Filament\Resources\EmailNotificationTemplates\Pages\EditEmailNotificationTemplate;
use App\Filament\Resources\EmailNotificationTemplates\Pages\ListEmailNotificationTemplates;
use App\Filament\Resources\EmailNotificationTemplates\Pages\ViewEmailNotificationTemplate;
use App\Filament\Resources\EmailNotificationTemplates\Schemas\EmailNotificationTemplateForm;
use App\Filament\Resources\EmailNotificationTemplates\Schemas\EmailNotificationTemplateInfolist;
use App\Filament\Resources\EmailNotificationTemplates\Tables\EmailNotificationTemplatesTable;
use App\Models\EmailNotificationTemplate;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailNotificationTemplateResource extends Resource
{
    protected static ?string $model = EmailNotificationTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Email templates';

    protected static ?int $navigationSort = 71;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return EmailNotificationTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailNotificationTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailNotificationTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailNotificationTemplates::route('/'),
            'view' => ViewEmailNotificationTemplate::route('/{record}'),
            'edit' => EditEmailNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
