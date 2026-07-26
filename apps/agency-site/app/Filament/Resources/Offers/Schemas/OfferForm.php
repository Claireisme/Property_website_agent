<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Support\PropertyOptions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Offer')
                    ->schema([
                        Select::make('property_id')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('buyer_access_request_id')
                            ->relationship('buyerAccessRequest', 'buyer_email')
                            ->label('Buyer access request')
                            ->searchable()
                            ->preload(),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('EUR'),
                        Select::make('status')
                            ->options(PropertyOptions::offerStatuses())
                            ->required()
                            ->default('submitted'),
                        TextInput::make('proof_document_path')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Buyer')
                    ->schema([
                        TextInput::make('buyer_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('buyer_email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('buyer_phone')
                            ->tel()
                            ->maxLength(255),
                        Select::make('buyer_position')
                            ->options(PropertyOptions::buyerPositions()),
                        Select::make('financing_type')
                            ->options(PropertyOptions::financingTypes()),
                        Select::make('mortgage_approval_status')
                            ->options([
                                'approved_in_principle' => 'Approved in principle',
                                'pending' => 'Pending',
                                'not_required' => 'Not required',
                            ]),
                        Select::make('current_property_status')
                            ->options(PropertyOptions::buyerPositions()),
                        TagsInput::make('conditions')
                            ->separator(',')
                            ->columnSpanFull(),
                        Textarea::make('message')
                            ->rows(5)
                            ->columnSpanFull(),
                        Toggle::make('consent_to_terms')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Timeline')
                    ->schema([
                        DateTimePicker::make('submitted_at'),
                        DateTimePicker::make('reviewed_at'),
                        DateTimePicker::make('accepted_at'),
                        DateTimePicker::make('rejected_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
