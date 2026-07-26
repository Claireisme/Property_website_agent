<?php

namespace App\Filament\Resources\Properties\Tables;

use App\Support\PropertyOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('published_at')
                    ->label('Published at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('teamMember.name')
                    ->label('Agent')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'under_offer', 'sale_agreed' => 'warning',
                        'sold' => 'info',
                        'withdrawn', 'archived' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('transaction_type')
                    ->label('For')
                    ->badge()
                    ->searchable(),
                TextColumn::make('listing_category')
                    ->label('Category')
                    ->state(fn ($record): string => PropertyOptions::listingCategories()[$record->listingCategory()] ?? 'All')
                    ->badge(),
                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PropertyOptions::statuses()),
                SelectFilter::make('transaction_type')
                    ->options(PropertyOptions::transactionTypes()),
                SelectFilter::make('property_type')
                    ->options(PropertyOptions::propertyTypes()),
                SelectFilter::make('listing_category')
                    ->label('Category')
                    ->options(PropertyOptions::listingCategories())
                    ->query(fn ($query, array $data) => $query->listingCategory($data['value'] ?? null)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
                ]),
            ]);
    }
}
