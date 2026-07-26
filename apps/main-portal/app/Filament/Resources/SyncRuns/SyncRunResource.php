<?php

namespace App\Filament\Resources\SyncRuns;

use App\Filament\Resources\SyncRuns\Pages\CreateSyncRun;
use App\Filament\Resources\SyncRuns\Pages\EditSyncRun;
use App\Filament\Resources\SyncRuns\Pages\ListSyncRuns;
use App\Filament\Resources\SyncRuns\Pages\ViewSyncRun;
use App\Filament\Resources\SyncRuns\Schemas\SyncRunForm;
use App\Filament\Resources\SyncRuns\Schemas\SyncRunInfolist;
use App\Filament\Resources\SyncRuns\Tables\SyncRunsTable;
use App\Models\SyncRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SyncRunResource extends Resource
{
    protected static ?string $model = SyncRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Sync runs';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return SyncRunForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SyncRunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SyncRunsTable::configure($table);
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
            'index' => ListSyncRuns::route('/'),
            'create' => CreateSyncRun::route('/create'),
            'view' => ViewSyncRun::route('/{record}'),
            'edit' => EditSyncRun::route('/{record}/edit'),
        ];
    }
}
