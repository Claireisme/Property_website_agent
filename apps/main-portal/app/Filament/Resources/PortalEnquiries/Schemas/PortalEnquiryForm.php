<?php

namespace App\Filament\Resources\PortalEnquiries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PortalEnquiryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Enquiry')
                    ->schema([
                        Select::make('portal_agency_id')
                            ->relationship('agency', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('portal_property_id')
                            ->relationship('property', 'title')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'forwarded' => 'Forwarded',
                                'closed' => 'Closed',
                                'spam' => 'Spam',
                            ])
                            ->required()
                            ->default('new'),
                        DateTimePicker::make('forwarded_at'),
                        Textarea::make('message')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
