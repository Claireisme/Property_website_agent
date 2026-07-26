<?php

namespace App\Filament\Resources\EmailSettings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmailSettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                IconEntry::make('mail_enabled')
                    ->boolean(),
                TextEntry::make('from_name')
                    ->label('From name')
                    ->placeholder('-'),
                TextEntry::make('from_email')
                    ->label('Verified sender email')
                    ->placeholder('-'),
                TextEntry::make('reply_to_email')
                    ->label('Reply-to email')
                    ->placeholder('-'),
                TextEntry::make('ses_region')
                    ->label('SES region')
                    ->placeholder('-'),
                TextEntry::make('smtp_username')
                    ->label('SMTP username')
                    ->placeholder('-'),
                TextEntry::make('smtp_port')
                    ->label('SMTP port')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
