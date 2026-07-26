<?php

namespace App\Filament\Resources\Properties\Schemas;

use App\Models\Property;
use App\Support\LocationOptions;
use App\Support\PropertyDescriptionFormatter;
use App\Support\PropertyOptions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PropertyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Photos')
                    ->description('Uploaded images attached to this property.')
                    ->schema([
                        ImageEntry::make('admin_gallery')
                            ->label('Uploaded photos')
                            ->state(fn (Property $record): array => $record
                                ->images()
                                ->orderBy('sort_order')
                                ->get()
                                ->map(fn ($image): ?string => $image->publicUrl($image->thumbnail_url ?: $image->card_url ?: $image->original_url))
                                ->filter()
                                ->values()
                                ->all())
                            ->visibility('public')
                            ->imageHeight(130)
                            ->wrap()
                            ->placeholder('No photos uploaded yet.')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                    ])
                    ->columnSpanFull(),
                Section::make('Listing')
                    ->schema([
                        TextEntry::make('title')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpanFull()
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('agency.name')
                            ->label('Agency')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('teamMember.name')
                            ->label('Responsible team member')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('public_id')
                            ->label('Public ID')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('slug')
                            ->placeholder('-')
                            ->copyable()
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'available' => 'success',
                                'under_offer', 'sale_agreed' => 'warning',
                                'sold' => 'info',
                                'withdrawn', 'archived' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => PropertyOptions::statuses()[$state] ?? ucfirst(str_replace('_', ' ', (string) $state)))
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('transaction_type')
                            ->label('Transaction type')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => PropertyOptions::transactionTypes()[$state] ?? ucfirst((string) $state))
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('property_type')
                            ->label('Property type')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?string $state): string => PropertyOptions::propertyTypes()[$state] ?? ucfirst(str_replace('_', ' ', (string) $state)))
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Price and features')
                    ->schema([
                        TextEntry::make('price')
                            ->money('EUR')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('price_qualifier')
                            ->label('Price qualifier')
                            ->formatStateUsing(fn (?string $state): string => PropertyOptions::priceQualifiers()[$state] ?? ucfirst(str_replace('_', ' ', (string) $state)))
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('bedrooms')
                            ->numeric()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('bathrooms')
                            ->numeric()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('floor_area_m2')
                            ->label('Floor area')
                            ->numeric()
                            ->suffix(' m2')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('ber_rating')
                            ->label('BER rating')
                            ->badge()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('features')
                            ->state(fn (Property $record): array => Property::normalizeFeatureList($record->features))
                            ->bulleted()
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry property-admin-list-entry']),
                        TextEntry::make('facilities')
                            ->state(fn (Property $record): array => Property::normalizeFeatureList($record->facilities))
                            ->badge()
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Address')
                    ->schema([
                        TextEntry::make('address_line_1')
                            ->label('Address')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('eircode')
                            ->label('Eircode')
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('region')
                            ->label('Region')
                            ->state(function (Property $record): ?string {
                                $region = LocationOptions::regionKeyFor($record->county, $record->town);

                                return $region ? LocationOptions::regions()[$region] ?? null : null;
                            })
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Description and publishing')
                    ->schema([
                        TextEntry::make('description')
                            ->markdown()
                            ->commonMarkOptions(PropertyDescriptionFormatter::commonMarkOptions())
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry property-admin-description-entry']),
                        TextEntry::make('viewing_notes')
                            ->label('Viewing notes')
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry property-admin-description-entry']),
                        TextEntry::make('published_at')
                            ->dateTime()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('sale_agreed_at')
                            ->label('Sale agreed at')
                            ->dateTime()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('sold_at')
                            ->dateTime()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-')
                            ->extraEntryWrapperAttributes(['class' => 'property-admin-detail-entry']),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
