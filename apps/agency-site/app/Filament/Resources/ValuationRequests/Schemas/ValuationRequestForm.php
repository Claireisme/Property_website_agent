<?php

namespace App\Filament\Resources\ValuationRequests\Schemas;

use App\Support\PropertyOptions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ValuationRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Owner details')
                    ->schema([
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
                        Select::make('preferred_contact_method')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'either' => 'Either',
                            ]),
                    ])
                    ->columns(2),
                Section::make('Property')
                    ->schema([
                        TextInput::make('property_address')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('eircode')
                            ->maxLength(255),
                        Select::make('property_type')
                            ->options(PropertyOptions::propertyTypes())
                            ->searchable(),
                        TextInput::make('bedrooms')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('bathrooms')
                            ->numeric()
                            ->minValue(0),
                        Select::make('selling_timeline')
                            ->options([
                                'asap' => 'As soon as possible',
                                '1_3_months' => '1-3 months',
                                '3_6_months' => '3-6 months',
                                '6_plus_months' => '6+ months',
                                'just_researching' => 'Just researching',
                            ]),
                    ])
                    ->columns(2),
                Section::make('Status')
                    ->schema([
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
