<?php

namespace App\Support;

class Locales
{
    public static function default(): string
    {
        return config('locales.default', 'en');
    }

    public static function supported(): array
    {
        return config('locales.supported', ['en' => 'English']);
    }

    public static function codes(): array
    {
        return array_keys(self::supported());
    }

    public static function nonDefaultCodes(): array
    {
        return array_values(array_filter(
            self::codes(),
            fn (string $locale): bool => $locale !== self::default(),
        ));
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, self::supported());
    }

    public static function label(string $locale): string
    {
        return self::supported()[$locale] ?? $locale;
    }

    public static function localizedRouteName(string $name, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === self::default() ? $name : 'localized.'.$name;
    }
}
