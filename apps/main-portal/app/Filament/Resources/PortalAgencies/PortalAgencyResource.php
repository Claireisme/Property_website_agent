<?php

namespace App\Filament\Resources\PortalAgencies;

use App\Filament\Resources\PortalAgencies\Pages\CreatePortalAgency;
use App\Filament\Resources\PortalAgencies\Pages\EditPortalAgency;
use App\Filament\Resources\PortalAgencies\Pages\ListPortalAgencies;
use App\Filament\Resources\PortalAgencies\Pages\ViewPortalAgency;
use App\Filament\Resources\PortalAgencies\Schemas\PortalAgencyForm;
use App\Filament\Resources\PortalAgencies\Schemas\PortalAgencyInfolist;
use App\Filament\Resources\PortalAgencies\Tables\PortalAgenciesTable;
use App\Models\PortalAgency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PortalAgencyResource extends Resource
{
    protected static ?string $model = PortalAgency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Agency feeds';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PortalAgencyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PortalAgencyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PortalAgenciesTable::configure($table);
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
            'index' => ListPortalAgencies::route('/'),
            'create' => CreatePortalAgency::route('/create'),
            'view' => ViewPortalAgency::route('/{record}'),
            'edit' => EditPortalAgency::route('/{record}/edit'),
        ];
    }
}
