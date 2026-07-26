<?php

use App\Http\Controllers\Api\Feed\PropertyFeedController;
use Illuminate\Support\Facades\Route;

Route::middleware('feed.token')->group(function (): void {
    Route::get('/feed/v1/properties', PropertyFeedController::class)
        ->name('api.feed.properties');
});
