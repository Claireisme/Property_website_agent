<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    'translation_gateway' => [
        'url' => env('TRANSLATION_GATEWAY_URL'),
        'token' => env('TRANSLATION_GATEWAY_TOKEN'),
        'auto_translate_properties' => env('AUTO_TRANSLATE_PROPERTIES', true),
    ],

    'media_api' => [
        'url' => env('MEDIA_API_URL'),
        'token' => env('MEDIA_API_TOKEN'),
        'tenant' => env('MEDIA_TENANT'),
        'canonical_base_url' => env('MEDIA_CANONICAL_BASE_URL', 'https://media.520.ie'),
        'brand_base_url' => env('MEDIA_BRAND_BASE_URL'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
