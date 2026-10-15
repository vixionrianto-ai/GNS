<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have a
    | conventional file to locate the various service credentials.
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

    'olt_event' => [\n        'token' => env('OLT_EVENT_TOKEN'),\n    ],\n\n    'olt' => [
        'enabled' => env('OLT_ENABLED', false),
        'base_url' => env('OLT_BASE_URL'),
        'username' => env('OLT_USERNAME'),
        'password' => env('OLT_PASSWORD'),
        'routers' => [
            'KUWU' => [
                'base_url' => env('OLT_KUWU_BASE_URL'),
                'username' => env('OLT_KUWU_USERNAME'),
                'password' => env('OLT_KUWU_PASSWORD'),
            ],
            'RUMAH' => [
                'base_url' => env('OLT_RUMAH_BASE_URL'),
                'username' => env('OLT_RUMAH_USERNAME'),
                'password' => env('OLT_RUMAH_PASSWORD'),
            ],
        ],
    ],

    'backup' => [
        'mysqldump_skip_ssl' => env('MYSQLDUMP_SKIP_SSL', false),
    ],
];
