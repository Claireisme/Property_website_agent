<?php

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SyncRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('portal_agency_id')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('success'),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('finished_at'),
                TextInput::make('listings_seen')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('listings_created')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('listings_updated')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('listings_removed')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('error_message')
                    ->columnSpanFull(),
            ]);
    }
}
