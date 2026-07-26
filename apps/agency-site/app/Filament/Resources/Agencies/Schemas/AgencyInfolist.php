<?php

namespace App\Filament\Resources\Agencies\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AgencyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('trading_name')
                    ->placeholder('-'),
                TextEntry::make('company_registration_number')
                    ->label('Company No.')
                    ->placeholder('-'),
                TextEntry::make('psra_licence_number')
                    ->placeholder('-'),
                TextEntry::make('website_domain')
                    ->placeholder('-'),
                TextEntry::make('logo_path')
                    ->placeholder('-'),
                ImageEntry::make('hero_image_path')
                    ->label('Hero background image')
                    ->disk('public')
                    ->imageHeight(120)
                    ->placeholder('-'),
                TextEntry::make('primary_colour'),
                TextEntry::make('secondary_colour'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('county')
                    ->placeholder('-'),
                TextEntry::make('eircode')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('theme_key'),
                TextEntry::make('facebook_url')
                    ->label('Facebook URL')
                    ->placeholder('-'),
                TextEntry::make('instagram_url')
                    ->label('Instagram URL')
                    ->placeholder('-'),
                TextEntry::make('youtube_url')
                    ->label('YouTube URL')
                    ->placeholder('-'),
                TextEntry::make('tiktok_url')
                    ->label('TikTok URL')
                    ->placeholder('-'),
                TextEntry::make('linkedin_url')
                    ->label('LinkedIn URL')
                    ->placeholder('-'),
                TextEntry::make('x_url')
                    ->label('X URL')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
