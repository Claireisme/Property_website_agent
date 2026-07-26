<?php

namespace App\Filament\Resources\BuyerAccessRequests\Schemas;

use App\Support\PropertyOptions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BuyerAccessRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Access request')
                    ->schema([
                        Select::make('property_id')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->options(PropertyOptions::buyerAccessStatuses())
                            ->required()
                            ->default('submitted'),
                        TextInput::make('initial_offer_amount')
                            ->label('Current offer')
                            ->prefix('EUR')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('proof_of_funds_path')
                            ->label('Proof of funds')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('identity_document_path')
                            ->label('Identity document')
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
                        TextInput::make('current_property_status')
                            ->maxLength(255),
                        Textarea::make('message')
                            ->rows(5)
                            ->columnSpanFull(),
                        Toggle::make('consent_to_terms')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Timeline')
                    ->schema([
                        DateTimePicker::make('requested_at'),
                        DateTimePicker::make('documents_uploaded_at'),
                        DateTimePicker::make('reviewed_at'),
                        DateTimePicker::make('approved_at'),
                        DateTimePicker::make('rejected_at'),
                    ])
                    ->columns(2),
            ]);
    }
}
