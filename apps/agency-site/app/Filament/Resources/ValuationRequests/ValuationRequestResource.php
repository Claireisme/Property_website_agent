<?php

namespace App\Filament\Resources\ValuationRequests;

use App\Filament\Resources\ValuationRequests\Pages\CreateValuationRequest;
use App\Filament\Resources\ValuationRequests\Pages\EditValuationRequest;
use App\Filament\Resources\ValuationRequests\Pages\ListValuationRequests;
use App\Filament\Resources\ValuationRequests\Pages\ViewValuationRequest;
use App\Filament\Resources\ValuationRequests\Schemas\ValuationRequestForm;
use App\Filament\Resources\ValuationRequests\Schemas\ValuationRequestInfolist;
use App\Filament\Resources\ValuationRequests\Tables\ValuationRequestsTable;
use App\Models\ValuationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ValuationRequestResource extends Resource
{
    protected static ?string $model = ValuationRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return ValuationRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ValuationRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValuationRequestsTable::configure($table);
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
            'index' => ListValuationRequests::route('/'),
            'create' => CreateValuationRequest::route('/create'),
            'view' => ViewValuationRequest::route('/{record}'),
            'edit' => EditValuationRequest::route('/{record}/edit'),
        ];
    }
}
