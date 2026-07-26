<?php

namespace App\Filament\Resources\PortalAgencies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PortalAgencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('website_url')
                    ->placeholder('-'),
                TextEntry::make('feed_url'),
                TextEntry::make('api_token_encrypted')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_sync_status')
                    ->placeholder('-'),
                TextEntry::make('last_error_message')
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
