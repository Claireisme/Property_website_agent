<?php

namespace App\Filament\Resources\PropertyImages\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PropertyImageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->label('Property'),
                TextEntry::make('original_url'),
                TextEntry::make('thumbnail_url')
                    ->placeholder('-'),
                TextEntry::make('card_url')
                    ->placeholder('-'),
                TextEntry::make('detail_url')
                    ->placeholder('-'),
                TextEntry::make('large_url')
                    ->placeholder('-'),
                TextEntry::make('caption')
                    ->placeholder('-'),
                TextEntry::make('sort_order')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
