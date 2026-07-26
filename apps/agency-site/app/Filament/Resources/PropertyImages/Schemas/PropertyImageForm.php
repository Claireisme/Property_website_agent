<?php

namespace App\Filament\Resources\PropertyImages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PropertyImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Image')
                    ->schema([
                        Select::make('property_id')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        FileUpload::make('original_url')
                            ->label('Image file')
                            ->image()
                            ->disk('public')
                            ->directory('properties/originals')
                            ->required(),
                        TextInput::make('caption')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make('Generated variants')
                    ->schema([
                        TextInput::make('thumbnail_url')
                            ->maxLength(255),
                        TextInput::make('card_url')
                            ->maxLength(255),
                        TextInput::make('detail_url')
                            ->maxLength(255),
                        TextInput::make('large_url')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
