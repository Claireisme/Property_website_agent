<?php

namespace App\Filament\Resources\PortalProperties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PortalPropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agency.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('transaction_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('property_type')
                    ->searchable(),
                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('bedrooms')
                    ->label('Beds')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bathrooms')
                    ->label('Baths')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('address_summary')
                    ->searchable(),
                TextColumn::make('town')
                    ->searchable(),
                TextColumn::make('county')
                    ->searchable(),
                TextColumn::make('source_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'under_offer' => 'Under offer',
                        'sale_agreed' => 'Sale agreed',
                        'sold' => 'Sold',
                        'withdrawn' => 'Withdrawn',
                    ]),
                SelectFilter::make('transaction_type')
                    ->options([
                        'sale' => 'Sale',
                        'rent' => 'Rent',
                        'commercial' => 'Commercial',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
