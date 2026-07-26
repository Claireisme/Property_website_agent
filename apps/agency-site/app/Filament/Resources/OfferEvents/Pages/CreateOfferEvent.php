<?php

namespace App\Filament\Resources\OfferEvents\Pages;

use App\Filament\Resources\OfferEvents\OfferEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOfferEvent extends CreateRecord
{
    protected static string $resource = OfferEventResource::class;
}
