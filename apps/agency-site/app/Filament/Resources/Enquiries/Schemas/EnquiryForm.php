<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Support\PropertyOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lead')
                    ->schema([
                        Select::make('property_id')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Select::make('enquiry_type')
                            ->label('Type')
                            ->options(PropertyOptions::enquiryTypes())
                            ->required()
                            ->default('question'),
                        Select::make('source')
                            ->options([
                                'agency_site' => 'Agency site',
                                'main_portal' => 'Main portal',
                            ])
                            ->required()
                            ->default('agency_site'),
                        Select::make('status')
                            ->options(PropertyOptions::leadStatuses())
                            ->required()
                            ->default('new'),
                        Textarea::make('message')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
