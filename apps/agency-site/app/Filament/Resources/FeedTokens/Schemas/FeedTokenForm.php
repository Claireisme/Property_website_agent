<?php

namespace App\Filament\Resources\FeedTokens\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Access')
                    ->schema([
                        TextInput::make('name')
                            ->disabled()
                            ->dehydrated(false),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        DateTimePicker::make('expires_at')
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('last_used_at')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }
}
