<?php

namespace App\Filament\Resources\Agencies\Schemas;

use App\Support\BidIncrementRules;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AgencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Agency details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('trading_name')
                            ->maxLength(255),
                        TextInput::make('company_registration_number')
                            ->label('Company No.')
                            ->maxLength(255),
                        TextInput::make('psra_licence_number')
                            ->label('PSRA licence number')
                            ->maxLength(255),
                        TextInput::make('website_domain')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Social media')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('x_url')
                            ->label('X URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Branding')
                    ->schema([
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('agency/logos'),
                        FileUpload::make('hero_image_path')
                            ->label('Hero background image')
                            ->image()
                            ->disk('public')
                            ->directory('agency/hero-images'),
                        ColorPicker::make('primary_colour')
                            ->required()
                            ->default('#0f766e'),
                        ColorPicker::make('secondary_colour')
                            ->required()
                            ->default('#111827'),
                        Select::make('theme_key')
                            ->options([
                                'classic' => 'Classic',
                                'modern' => 'Modern',
                                'editorial' => 'Editorial',
                            ])
                            ->required()
                            ->default('classic'),
                    ])
                    ->columns(2),
                Section::make('Location and copy')
                    ->schema([
                        TextInput::make('address')
                            ->maxLength(255),
                        TextInput::make('county')
                            ->maxLength(255),
                        TextInput::make('eircode')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Online bidding increments')
                    ->description('Set the minimum offer increase by asking-price range. Leave the upper limit empty for the final open-ended range.')
                    ->schema([
                        Repeater::make('bid_increment_rules')
                            ->label('Bid increment ranges')
                            ->schema([
                                TextInput::make('min_price')
                                    ->label('From asking price')
                                    ->prefix('EUR')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                TextInput::make('max_price')
                                    ->label('To asking price')
                                    ->prefix('EUR')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('increment_amount')
                                    ->label('Minimum increase')
                                    ->prefix('EUR')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->default(BidIncrementRules::defaults())
                            ->mutateDehydratedStateUsing(fn (?array $state): array => BidIncrementRules::normalize($state))
                            ->columns(3)
                            ->addActionLabel('Add price range')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
