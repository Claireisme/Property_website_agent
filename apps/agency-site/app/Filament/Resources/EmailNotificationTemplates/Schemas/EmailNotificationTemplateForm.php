<?php

namespace App\Filament\Resources\EmailNotificationTemplates\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailNotificationTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template')
                    ->description('Use variables such as {{ property_title }} or {{ offer_amount }}. Available variables are listed on the view page.')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Template enabled')
                            ->default(true),
                        TextInput::make('key')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('label')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('audience')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->required()
                            ->rows(10)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
