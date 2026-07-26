<?php

namespace App\Filament\Resources\FeedTokens\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class FeedTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('name'),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('gray'),
                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
