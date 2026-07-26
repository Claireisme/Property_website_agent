<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('properties:translate')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer()
    ->when(fn (): bool => (bool) config('services.translation_gateway.auto_translate_properties', true)
        && (filled(config('services.translation_gateway.url')) || filled(config('services.deepseek.key'))));
