<?php

namespace App\Filament\Resources\TranslationProviderSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TranslationProviderSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Provider')
                    ->schema([
                        TextInput::make('provider')
                            ->required()
                            ->maxLength(255)
                            ->default('deepseek'),
                        Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(false),
                        TextInput::make('api_key')
                            ->label('API key')
                            ->password()
                            ->revealable()
                            ->helperText('Stored encrypted. Leave blank to use DEEPSEEK_API_KEY from the main portal environment.'),
                        TextInput::make('base_url')
                            ->url()
                            ->required()
                            ->default('https://api.deepseek.com')
                            ->maxLength(255),
                        TextInput::make('model')
                            ->required()
                            ->default('deepseek-chat')
                            ->maxLength(255),
                        TextInput::make('timeout_seconds')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(300)
                            ->default(90),
                    ])
                    ->columns(2),
            ]);
    }
}
