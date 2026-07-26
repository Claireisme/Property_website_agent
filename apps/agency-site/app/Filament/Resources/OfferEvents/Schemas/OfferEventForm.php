<?php

namespace App\Filament\Resources\OfferEvents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfferEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('offer_id')
                    ->relationship('offer', 'id')
                    ->required(),
                TextInput::make('actor_type')
                    ->required()
                    ->default('buyer'),
                TextInput::make('actor_id')
                    ->numeric(),
                TextInput::make('event_type')
                    ->required(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
