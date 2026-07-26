<?php

namespace App\Filament\Resources\SyncRuns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SyncRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('portal_agency_id')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('finished_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('listings_seen')
                    ->numeric(),
                TextEntry::make('listings_created')
                    ->numeric(),
                TextEntry::make('listings_updated')
                    ->numeric(),
                TextEntry::make('listings_removed')
                    ->numeric(),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
