<?php

namespace App\Filament\Resources\OfferEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OfferEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('offer.id')
                    ->label('Offer'),
                TextEntry::make('actor_type'),
                TextEntry::make('actor_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('event_type'),
                TextEntry::make('metadata')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
