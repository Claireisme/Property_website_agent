<?php

namespace App\Support;

use Illuminate\Support\Str;

class PropertyDescriptionFormatter
{
    public static function commonMarkOptions(): array
    {
        return [
            'allow_unsafe_links' => false,
            'html_input' => 'strip',
            'renderer' => [
                'soft_break' => "<br>\n",
            ],
        ];
    }

    public static function cleanMarkdownInput(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace(["\r\n", "\r"], "\n", $value);

        if (str_contains($value, '<')) {
            $value = preg_replace('/<\s*br\s*\/?>/i', "\n", $value) ?? $value;
            $value = preg_replace('/<\/\s*(p|div|li|h[1-6]|tr)\s*>/i', "\n", $value) ?? $value;
            $value = strip_tags($value);
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $value = preg_replace("/[ \t]+\n/", "\n", $value) ?? $value;
        $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public static function toHtml(?string $markdown): string
    {
        $markdown = trim((string) $markdown);

        if ($markdown === '') {
            return '';
        }

        $html = Str::markdown($markdown, self::commonMarkOptions());

        return Str::sanitizeHtml($html);
    }
}
