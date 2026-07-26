<?php

namespace App\Filament\Resources\Offers\Tables;

use App\Support\PropertyOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OffersTable
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
                TextColumn::make('buyerAccessRequest.status')
                    ->label('Access')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('financing_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('proof_document_path')
                    ->label('Proof')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('consent_to_terms')
                    ->boolean(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rejected_at')
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
                    ->options(PropertyOptions::offerStatuses()),
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
            ]);
    }
}
