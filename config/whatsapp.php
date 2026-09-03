<?php

return [
    'enabled' => (bool) env('WHATSAPP_ENABLED', true),
    'provider' => env('WHATSAPP_PROVIDER', 'fonnte'),
    'fonnte' => [
        'token' => env('WHATSAPP_FONNTE_TOKEN'),
        'endpoint' => env('WHATSAPP_FONNTE_ENDPOINT', 'https://api.fonnte.com/send'),
    ],
    'template_isolir' => env('WHATSAPP_TEMPLATE_ISOLIR', ''),
];