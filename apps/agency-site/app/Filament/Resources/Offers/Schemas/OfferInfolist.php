<?php

namespace App\Filament\Resources\Offers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OfferInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->label('Property'),
                TextEntry::make('buyerAccessRequest.status')
                    ->label('Buyer access')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('buyer_name'),
                TextEntry::make('buyer_email'),
                TextEntry::make('buyer_phone')
                    ->placeholder('-'),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('buyer_position')
                    ->placeholder('-'),
                TextEntry::make('financing_type')
                    ->placeholder('-'),
                TextEntry::make('mortgage_approval_status')
                    ->placeholder('-'),
                TextEntry::make('current_property_status')
                    ->placeholder('-'),
                TextEntry::make('conditions')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('proof_document_path')
                    ->placeholder('-'),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('consent_to_terms')
                    ->boolean(),
                TextEntry::make('submitted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('reviewed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejected_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
