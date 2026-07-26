<?php

namespace App\Filament\Resources\EmailSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                IconColumn::make('mail_enabled')
                    ->label('Enabled')
                    ->boolean(),
                TextColumn::make('from_email')
                    ->label('Verified sender')
                    ->placeholder('-'),
                TextColumn::make('ses_region')
                    ->label('SES region')
                    ->placeholder('-'),
                TextColumn::make('reply_to_email')
                    ->label('Reply-to')
                    ->placeholder('-'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
