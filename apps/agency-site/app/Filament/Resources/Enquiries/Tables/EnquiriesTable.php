<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Support\PropertyOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('property.title')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('enquiry_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => PropertyOptions::enquiryTypes()[$state] ?? ucfirst((string) $state))
                    ->badge()
                    ->searchable(),
                TextColumn::make('source')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options([
                        'agency_site' => 'Agency site',
                        'main_portal' => 'Main portal',
                    ]),
                SelectFilter::make('enquiry_type')
                    ->label('Type')
                    ->options(PropertyOptions::enquiryTypes()),
                SelectFilter::make('status')
                    ->options(PropertyOptions::leadStatuses()),
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
