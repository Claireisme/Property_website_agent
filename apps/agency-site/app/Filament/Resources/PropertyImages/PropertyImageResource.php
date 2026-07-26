<?php

namespace App\Filament\Resources\PropertyImages;

use App\Filament\Resources\PropertyImages\Pages\CreatePropertyImage;
use App\Filament\Resources\PropertyImages\Pages\EditPropertyImage;
use App\Filament\Resources\PropertyImages\Pages\ListPropertyImages;
use App\Filament\Resources\PropertyImages\Pages\ViewPropertyImage;
use App\Filament\Resources\PropertyImages\Schemas\PropertyImageForm;
use App\Filament\Resources\PropertyImages\Schemas\PropertyImageInfolist;
use App\Filament\Resources\PropertyImages\Tables\PropertyImagesTable;
use App\Models\PropertyImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PropertyImageResource extends Resource
{
    protected static ?string $model = PropertyImage::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Property images';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return PropertyImageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PropertyImageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyImagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyImages::route('/'),
            'create' => CreatePropertyImage::route('/create'),
            'view' => ViewPropertyImage::route('/{record}'),
            'edit' => EditPropertyImage::route('/{record}/edit'),
        ];
    }
}
