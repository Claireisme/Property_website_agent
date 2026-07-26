<?php

namespace App\Filament\Resources\PortalAgencies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PortalAgencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feed access')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('website_url')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('feed_url')
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Textarea::make('api_token_encrypted')
                            ->label('Bearer token')
                            ->required()
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'cancelled' => 'Cancelled',
                                'error' => 'Error',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),
                Section::make('Last sync')
                    ->schema([
                        DateTimePicker::make('last_synced_at')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('last_sync_status')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('last_error_message')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
