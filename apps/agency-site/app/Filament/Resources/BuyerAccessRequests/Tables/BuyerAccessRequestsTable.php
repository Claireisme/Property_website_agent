<?php

namespace App\Filament\Resources\BuyerAccessRequests\Tables;

use App\Support\PropertyOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BuyerAccessRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.title')
                    ->searchable(),
                TextColumn::make('buyer_name')
                    ->searchable(),
                TextColumn::make('buyer_email')
                    ->searchable(),
                TextColumn::make('buyer_phone')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('initial_offer_amount')
                    ->label('Current offer')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('financing_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('proof_of_funds_path')
                    ->label('Proof')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('identity_document_path')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('consent_to_terms')
                    ->boolean(),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rejected_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PropertyOptions::buyerAccessStatuses()),
                SelectFilter::make('financing_type')
                    ->options(PropertyOptions::financingTypes()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('requested_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }
}
