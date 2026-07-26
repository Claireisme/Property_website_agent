<?php

namespace App\Filament\Resources\PortalProperties\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PortalPropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('portal_agency_id')
                    ->required()
                    ->numeric(),
                TextInput::make('external_listing_id')
                    ->required(),
                TextInput::make('source_url')
                    ->url(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('transaction_type'),
                TextInput::make('property_type'),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('bedrooms')
                    ->numeric(),
                TextInput::make('bathrooms')
                    ->numeric(),
                TextInput::make('floor_area_m2')
                    ->numeric(),
                TextInput::make('ber_rating'),
                TextInput::make('address_summary'),
                TextInput::make('town'),
                TextInput::make('county'),
                TextInput::make('eircode_hash'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('images')
                    ->columnSpanFull(),
                Textarea::make('features')
                    ->columnSpanFull(),
                DateTimePicker::make('source_updated_at'),
                DateTimePicker::make('first_synced_at'),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}
