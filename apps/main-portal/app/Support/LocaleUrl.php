<?php

namespace App\Support;

class LocaleUrl
{
    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $locale = app()->getLocale();
        $routeName = Locales::localizedRouteName($name, $locale);

        return route($routeName, self::parametersWithLocale($parameters, $locale), $absolute);
    }

    public static function switchUrl(string $targetLocale): string
    {
        $segments = request()->segments();

        if ($segments !== [] && in_array($segments[0], Locales::nonDefaultCodes(), true)) {
            array_shift($segments);
        }

        $path = implode('/', $segments);
        $url = $targetLocale === Locales::default()
            ? url($path === '' ? '/' : $path)
            : url($targetLocale.($path === '' ? '' : '/'.$path));

        $query = request()->getQueryString();

        return $query ? $url.'?'.$query : $url;
    }

    private static function parametersWithLocale(mixed $parameters, string $locale): mixed
    {
        if ($locale === Locales::default()) {
            return $parameters;
        }

        if (! is_array($parameters)) {
            return ['locale' => $locale, $parameters];
        }

        if (array_is_list($parameters)) {
            return array_merge(['locale' => $locale], $parameters);
        }

        return ['locale' => $locale] + $parameters;
    }
}
