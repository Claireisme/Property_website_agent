<?php

use App\Http\Controllers\Api\Internal\TranslationGatewayController;
use Illuminate\Support\Facades\Route;

Route::middleware('translation.gateway')->group(function (): void {
    Route::post('/internal/translations/property', TranslationGatewayController::class)
        ->name('api.internal.translations.property');
});
