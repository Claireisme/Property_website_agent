<?php

namespace App\Filament\Resources\BuyerAccessRequests;

use App\Filament\Resources\BuyerAccessRequests\Pages\CreateBuyerAccessRequest;
use App\Filament\Resources\BuyerAccessRequests\Pages\EditBuyerAccessRequest;
use App\Filament\Resources\BuyerAccessRequests\Pages\ListBuyerAccessRequests;
use App\Filament\Resources\BuyerAccessRequests\Pages\ViewBuyerAccessRequest;
use App\Filament\Resources\BuyerAccessRequests\Schemas\BuyerAccessRequestForm;
use App\Filament\Resources\BuyerAccessRequests\Schemas\BuyerAccessRequestInfolist;
use App\Filament\Resources\BuyerAccessRequests\Tables\BuyerAccessRequestsTable;
use App\Models\BuyerAccessRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BuyerAccessRequestResource extends Resource
{
    protected static ?string $model = BuyerAccessRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Buyer access';

    protected static ?int $navigationSort = 34;

    public static function form(Schema $schema): Schema
    {
        return BuyerAccessRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BuyerAccessRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BuyerAccessRequestsTable::configure($table);
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
            'index' => ListBuyerAccessRequests::route('/'),
            'create' => CreateBuyerAccessRequest::route('/create'),
            'view' => ViewBuyerAccessRequest::route('/{record}'),
            'edit' => EditBuyerAccessRequest::route('/{record}/edit'),
        ];
    }
}
