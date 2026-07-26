<?php

namespace App\Filament\Resources\PortalProperties;

use App\Filament\Resources\PortalProperties\Pages\CreatePortalProperty;
use App\Filament\Resources\PortalProperties\Pages\EditPortalProperty;
use App\Filament\Resources\PortalProperties\Pages\ListPortalProperties;
use App\Filament\Resources\PortalProperties\Pages\ViewPortalProperty;
use App\Filament\Resources\PortalProperties\Schemas\PortalPropertyForm;
use App\Filament\Resources\PortalProperties\Schemas\PortalPropertyInfolist;
use App\Filament\Resources\PortalProperties\Tables\PortalPropertiesTable;
use App\Models\PortalProperty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PortalPropertyResource extends Resource
{
    protected static ?string $model = PortalProperty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $navigationLabel = 'Synced properties';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return PortalPropertyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PortalPropertyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PortalPropertiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortalProperties::route('/'),
            'create' => CreatePortalProperty::route('/create'),
            'view' => ViewPortalProperty::route('/{record}'),
            'edit' => EditPortalProperty::route('/{record}/edit'),
        ];
    }
}
