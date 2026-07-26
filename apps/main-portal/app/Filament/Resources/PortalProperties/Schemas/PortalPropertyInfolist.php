<?php

namespace App\Filament\Resources\PortalProperties\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PortalPropertyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('portal_agency_id')
                    ->numeric(),
                TextEntry::make('external_listing_id'),
                TextEntry::make('source_url')
                    ->placeholder('-'),
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('status'),
                TextEntry::make('transaction_type')
                    ->placeholder('-'),
                TextEntry::make('property_type')
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('bedrooms')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('bathrooms')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('floor_area_m2')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ber_rating')
                    ->placeholder('-'),
                TextEntry::make('address_summary')
                    ->placeholder('-'),
                TextEntry::make('town')
                    ->placeholder('-'),
                TextEntry::make('county')
                    ->placeholder('-'),
                TextEntry::make('eircode_hash')
                    ->placeholder('-'),
                TextEntry::make('latitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('longitude')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('images')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('features')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('source_updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('first_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
