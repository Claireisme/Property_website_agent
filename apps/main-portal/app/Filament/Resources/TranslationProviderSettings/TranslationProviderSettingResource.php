<?php

namespace App\Filament\Resources\TranslationProviderSettings;

use App\Filament\Resources\TranslationProviderSettings\Pages\CreateTranslationProviderSetting;
use App\Filament\Resources\TranslationProviderSettings\Pages\EditTranslationProviderSetting;
use App\Filament\Resources\TranslationProviderSettings\Pages\ListTranslationProviderSettings;
use App\Filament\Resources\TranslationProviderSettings\Schemas\TranslationProviderSettingForm;
use App\Filament\Resources\TranslationProviderSettings\Tables\TranslationProviderSettingsTable;
use App\Models\TranslationProviderSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TranslationProviderSettingResource extends Resource
{
    protected static ?string $model = TranslationProviderSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?string $navigationLabel = 'Translation settings';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return TranslationProviderSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TranslationProviderSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTranslationProviderSettings::route('/'),
            'create' => CreateTranslationProviderSetting::route('/create'),
            'edit' => EditTranslationProviderSetting::route('/{record}/edit'),
        ];
    }
}
