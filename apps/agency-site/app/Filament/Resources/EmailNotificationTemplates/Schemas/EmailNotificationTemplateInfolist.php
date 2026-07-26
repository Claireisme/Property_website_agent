<?php

namespace App\Filament\Resources\EmailNotificationTemplates\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmailNotificationTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IconEntry::make('is_enabled')
                    ->boolean(),
                TextEntry::make('key')
                    ->copyable(),
                TextEntry::make('label'),
                TextEntry::make('audience')
                    ->badge(),
                TextEntry::make('subject')
                    ->columnSpanFull(),
                TextEntry::make('body')
                    ->columnSpanFull(),
                TextEntry::make('available_variables')
                    ->badge()
                    ->separator(',')
                    ->columnSpanFull(),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
