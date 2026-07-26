<?php

namespace App\Filament\Resources\Properties\Schemas;

use App\Support\LocationOptions;
use App\Support\PropertyDescriptionFormatter;
use App\Support\PropertyOptions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::editSections());
    }

    public static function steps(): array
    {
        return [
            Step::make('Location')
                ->description('Confirm the agency, region, locality, and address first.')
                ->schema([
                    Section::make('Agency and location')
                        ->description('Start with the listing owner and the public location details. The locality list updates after a region is selected.')
                        ->schema(self::locationFields())
                        ->columnSpanFull()
                        ->columns(2),
                ]),

            Step::make('Details')
                ->description('Set the title, status, pricing, and core property facts.')
                ->schema([
                    Section::make('Listing details')
                        ->schema(self::listingDetailsFields())
                        ->columnSpanFull()
                        ->columns(2),

                    Section::make('Price and facts')
                        ->schema(self::priceAndFactsFields())
                        ->columnSpanFull()
                        ->columns(3),
                ]),

            Step::make('Images & media')
                ->description('Upload listing photos and optional floorplans before writing the description.')
                ->schema([
                    Section::make('Photos')
                        ->description('Select multiple photos at once. They will be added to this property when you save the listing.')
                        ->schema(self::mediaFields())
                        ->columnSpanFull(),
                ]),

            Step::make('Description')
                ->description('Add features, buyer notes, and publishing controls.')
                ->schema([
                    Section::make('Features')
                        ->schema(self::featureFields())
                        ->columnSpanFull(),

                    Section::make('Description and publishing')
                        ->schema(self::descriptionPublishingFields())
                        ->columnSpanFull()
                        ->columns(3),
                ]),
        ];
    }

    public static function editSections(): array
    {
        return [
            Section::make('01 Location')
                ->description('Agency ownership, region, locality, and address details.')
                ->schema(self::locationFields())
                ->columns(2)
                ->columnSpanFull(),

            Section::make('02 Details')
                ->description('Listing headline, status, pricing, and core property facts.')
                ->schema([
                    Fieldset::make('Listing details')
                        ->schema(self::listingDetailsFields())
                        ->columns(2)
                        ->columnSpanFull(),
                    Fieldset::make('Price and facts')
                        ->schema(self::priceAndFactsFields())
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('03 Images & media')
                ->description('Upload new listing photos and optional floorplans for this property.')
                ->schema(self::editMediaFields())
                ->columnSpanFull(),

            Section::make('04 Description')
                ->description('Features, facilities, buyer notes, and publishing controls.')
                ->schema([
                    Fieldset::make('Features')
                        ->schema(self::featureFields())
                        ->columns(1)
                        ->columnSpanFull(),
                    Fieldset::make('Description and publishing')
                        ->schema(self::descriptionPublishingFields())
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }

    protected static function locationFields(): array
    {
        return [
            Select::make('agency_id')
                ->relationship('agency', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('team_member_id')
                ->label('Responsible team member')
                ->relationship('teamMember', 'name')
                ->searchable()
                ->preload()
                ->default(fn (): ?int => auth()->user()?->activeTeamMember()?->id)
                ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
            Select::make('region')
                ->label('Region')
                ->options(LocationOptions::regionFormOptions())
                ->searchable()
                ->preload()
                ->live()
                ->required()
                ->helperText('Dublin is intentionally listed first, followed by county and sub-area choices.')
                ->afterStateHydrated(function (Select $component, $record): void {
                    $component->state(LocationOptions::regionKeyFor($record?->county, $record?->town));
                })
                ->afterStateUpdated(fn (Set $set) => $set('locality', null)),
            Select::make('locality')
                ->label('Locality')
                ->options(fn (Get $get): array => LocationOptions::localitiesForRegion($get('region')))
                ->searchable()
                ->preload()
                ->required(fn (Get $get): bool => LocationOptions::localitiesForRegion($get('region')) !== [])
                ->disabled(fn (Get $get): bool => LocationOptions::localitiesForRegion($get('region')) === [])
                ->placeholder(fn (Get $get): string => LocationOptions::localitiesForRegion($get('region')) === []
                    ? 'No locality needed for this region'
                    : 'Select locality')
                ->afterStateHydrated(function (Select $component, $record): void {
                    $region = LocationOptions::regionKeyFor($record?->county, $record?->town);
                    $localities = LocationOptions::localitiesForRegion($region);

                    $component->state(array_key_exists((string) $record?->town, $localities) ? $record?->town : null);
                }),
            TextInput::make('address_line_1')
                ->label('Address')
                ->required()
                ->maxLength(255),
            TextInput::make('eircode')
                ->label('Eircode')
                ->maxLength(255),
        ];
    }

    protected static function listingDetailsFields(): array
    {
        return [
            TextInput::make('title')
                ->helperText('Use the public headline buyers will see on the website.')
                ->required()
                ->maxLength(255),
            Select::make('status')
                ->options(PropertyOptions::statuses())
                ->required()
                ->default('available'),
            Select::make('transaction_type')
                ->options(PropertyOptions::transactionTypes())
                ->required()
                ->default('sale'),
            Select::make('property_type')
                ->options(PropertyOptions::propertyTypes())
                ->searchable()
                ->required(),
        ];
    }

    protected static function priceAndFactsFields(): array
    {
        return [
            TextInput::make('price')
                ->numeric()
                ->prefix('EUR'),
            Select::make('price_qualifier')
                ->options(PropertyOptions::priceQualifiers())
                ->required()
                ->default('asking_price'),
            TextInput::make('bedrooms')
                ->numeric()
                ->minValue(0),
            TextInput::make('bathrooms')
                ->numeric()
                ->minValue(0),
            TextInput::make('floor_area_m2')
                ->numeric()
                ->suffix('m2'),
            Select::make('ber_rating')
                ->options(PropertyOptions::berRatings())
                ->searchable(),
            Toggle::make('online_offers_enabled')
                ->label('Online offers enabled')
                ->default(false),
        ];
    }

    protected static function mediaFields(): array
    {
        return [
            FileUpload::make('bulk_photo_uploads')
                ->label('Bulk upload photos')
                ->helperText('You can select multiple image files in one go. The upload order becomes the initial public display order.')
                ->extraAttributes(['class' => 'property-bulk-photo-upload'])
                ->image()
                ->multiple()
                ->appendFiles()
                ->reorderable()
                ->maxFiles(60)
                ->disk('public')
                ->directory('properties/originals')
                ->panelLayout('grid')
                ->itemPanelAspectRatio('16:10')
                ->imagePreviewHeight('180')
                ->columnSpanFull(),
            FileUpload::make('floorplan_uploads')
                ->label('Floorplans')
                ->helperText('Optional. Floorplans are saved after the main photos and can be reordered later from the image manager.')
                ->extraAttributes(['class' => 'property-floorplan-upload'])
                ->image()
                ->multiple()
                ->appendFiles()
                ->reorderable()
                ->maxFiles(10)
                ->disk('public')
                ->directory('properties/floorplans')
                ->panelLayout('grid')
                ->itemPanelAspectRatio('4:3')
                ->imagePreviewHeight('180')
                ->columnSpanFull(),
        ];
    }

    protected static function editMediaFields(): array
    {
        return [
            Repeater::make('images')
                ->label('Uploaded photos')
                ->relationship('images', modifyQueryUsing: fn ($query) => $query->orderBy('sort_order'))
                ->extraAttributes(['class' => 'property-photo-repeater'])
                ->schema([
                    Hidden::make('original_url')
                        ->required(),
                    Placeholder::make('photo_preview')
                        ->label('Photo')
                        ->content(function (Get $get): HtmlString {
                            $url = self::imagePreviewUrl($get('original_url'));

                            if ($url === null) {
                                return new HtmlString('<div class="property-existing-photo-preview property-existing-photo-preview-empty">No photo preview available</div>');
                            }

                            return new HtmlString(sprintf(
                                '<div class="property-existing-photo-preview"><img src="%s" alt="Property photo preview" loading="lazy"></div>',
                                e($url),
                            ));
                        })
                        ->columnSpanFull(),
                    TextInput::make('caption')
                        ->label('Caption')
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->helperText('Drag photos or use the move buttons to control the public display order. Deleting photos requires an administrator account.')
                ->itemLabel(fn (array $state): ?string => filled($state['caption'] ?? null)
                    ? $state['caption']
                    : 'Property photo')
                ->itemNumbers()
                ->addable(false)
                ->deletable(fn (): bool => auth()->user()?->isAdministrator() ?? false)
                ->orderColumn('sort_order')
                ->reorderableWithButtons()
                ->reorderableWithDragAndDrop()
                ->grid([
                    'sm' => 2,
                    'lg' => 3,
                    'xl' => 4,
                    '2xl' => 5,
                ])
                ->defaultItems(0)
                ->columnSpanFull(),
            ...self::mediaFields(),
        ];
    }

    protected static function imagePreviewUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '/')
        ) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    protected static function featureFields(): array
    {
        return [
            TagsInput::make('features')
                ->separator(',')
                ->columnSpanFull(),
            Select::make('facilities')
                ->multiple()
                ->options(PropertyOptions::facilities())
                ->searchable()
                ->columnSpanFull(),
        ];
    }

    protected static function descriptionPublishingFields(): array
    {
        return [
            MarkdownEditor::make('description')
                ->toolbarButtons([
                    ['bold', 'italic'],
                    ['bulletList', 'orderedList'],
                    ['undo', 'redo'],
                ])
                ->commonMarkOptions(PropertyDescriptionFormatter::commonMarkOptions())
                ->helperText('Supports line breaks, bold, italic, and simple lists. Word colours, fonts, sizes, and other pasted styling are stripped for a consistent listing style.')
                ->minHeight('260px')
                ->columnSpanFull(),
            Textarea::make('viewing_notes')
                ->rows(4)
                ->columnSpanFull(),
            DateTimePicker::make('published_at')
                ->default(fn () => now()),
            DateTimePicker::make('sale_agreed_at'),
            DateTimePicker::make('sold_at'),
            TextInput::make('public_id')
                ->disabled(fn (string $operation): bool => $operation === 'edit')
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->helperText(fn (string $operation): string => $operation === 'edit'
                    ? 'Generated when the property was created and kept fixed for feeds, links, and integrations.'
                    : 'Generated automatically when left blank.')
                ->maxLength(255),
            TextInput::make('slug')
                ->disabled(fn (string $operation): bool => $operation === 'edit')
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->helperText(fn (string $operation): string => $operation === 'edit'
                    ? 'Generated when the property was created and kept fixed so existing property links remain stable.'
                    : 'Generated automatically when left blank.')
                ->maxLength(255),
        ];
    }
}
