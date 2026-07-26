<?php

namespace App\Filament\Resources\PortalEnquiries;

use App\Filament\Resources\PortalEnquiries\Pages\CreatePortalEnquiry;
use App\Filament\Resources\PortalEnquiries\Pages\EditPortalEnquiry;
use App\Filament\Resources\PortalEnquiries\Pages\ListPortalEnquiries;
use App\Filament\Resources\PortalEnquiries\Schemas\PortalEnquiryForm;
use App\Filament\Resources\PortalEnquiries\Tables\PortalEnquiriesTable;
use App\Models\PortalEnquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PortalEnquiryResource extends Resource
{
    protected static ?string $model = PortalEnquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Portal enquiries';

    protected static ?int $navigationSort = 25;

    public static function form(Schema $schema): Schema
    {
        return PortalEnquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PortalEnquiriesTable::configure($table);
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
            'index' => ListPortalEnquiries::route('/'),
            'create' => CreatePortalEnquiry::route('/create'),
            'edit' => EditPortalEnquiry::route('/{record}/edit'),
        ];
    }
}
