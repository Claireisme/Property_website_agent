<?php

namespace App\Filament\Resources\OfferEvents;

use App\Filament\Resources\OfferEvents\Pages\CreateOfferEvent;
use App\Filament\Resources\OfferEvents\Pages\EditOfferEvent;
use App\Filament\Resources\OfferEvents\Pages\ListOfferEvents;
use App\Filament\Resources\OfferEvents\Pages\ViewOfferEvent;
use App\Filament\Resources\OfferEvents\Schemas\OfferEventForm;
use App\Filament\Resources\OfferEvents\Schemas\OfferEventInfolist;
use App\Filament\Resources\OfferEvents\Tables\OfferEventsTable;
use App\Models\OfferEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OfferEventResource extends Resource
{
    protected static ?string $model = OfferEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Offer events';

    protected static ?int $navigationSort = 36;

    public static function form(Schema $schema): Schema
    {
        return OfferEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OfferEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfferEventsTable::configure($table);
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
            'index' => ListOfferEvents::route('/'),
            'create' => CreateOfferEvent::route('/create'),
            'view' => ViewOfferEvent::route('/{record}'),
            'edit' => EditOfferEvent::route('/{record}/edit'),
        ];
    }
}
