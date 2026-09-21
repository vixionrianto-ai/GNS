<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for these credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
    ],

    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'olt' => [
        'enabled' => env('OLT_ENABLED', false),
        'base_url' => env('OLT_BASE_URL'),
        'username' => env('OLT_USERNAME'),
        'password' => env('OLT_PASSWORD'),
        'timeout' => env('OLT_TIMEOUT', 10),

        // Optional per-router OLT configuration.
        // Example:
        // OLT_ROUTERS='{"KUWU":{"base_url":"http://olt-kuwu:725"},"RUMAH":{"base_url":"http://olt-rumah:725"}}'
        'routers' => json_decode(env('OLT_ROUTERS', '{}'), true) ?: [],
    ],

    'backup' => [
        'mysqldump_skip_ssl' => env('MYSQLDUMP_SKIP_SSL', false),
    ],
];