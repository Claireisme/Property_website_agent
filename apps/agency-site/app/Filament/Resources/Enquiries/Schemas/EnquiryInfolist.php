<?php

namespace App\Filament\Resources\Enquiries\Schemas;

use App\Support\PropertyOptions;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EnquiryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->label('Property')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('enquiry_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => PropertyOptions::enquiryTypes()[$state] ?? ucfirst((string) $state))
                    ->badge(),
                TextEntry::make('message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('source'),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
