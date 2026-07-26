<?php

namespace App\Filament\Resources\FeedTokens;

use App\Filament\Resources\FeedTokens\Pages\ListFeedTokens;
use App\Filament\Resources\FeedTokens\Pages\ViewFeedToken;
use App\Filament\Resources\FeedTokens\Schemas\FeedTokenForm;
use App\Filament\Resources\FeedTokens\Schemas\FeedTokenInfolist;
use App\Filament\Resources\FeedTokens\Tables\FeedTokensTable;
use App\Models\FeedToken;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedTokenResource extends Resource
{
    protected static ?string $model = FeedToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Feed tokens';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return FeedTokenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FeedTokenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedTokensTable::configure($table);
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
            'index' => ListFeedTokens::route('/'),
            'view' => ViewFeedToken::route('/{record}'),
        ];
    }
}
