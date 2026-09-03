<?php

return [
    // Otomatis WhatsApp sengaja OFF sampai diaktifkan oleh admin.
    'enabled' => (bool) env('WHATSAPP_ENABLED', false),
    'provider' => env('WHATSAPP_PROVIDER', 'fonnte'),
    'fonnte' => [
        'token' => env('WHATSAPP_FONNTE_TOKEN'),
        'endpoint' => env('WHATSAPP_FONNTE_ENDPOINT', 'https://api.fonnte.com/send'),
    ],
    'template_isolir' => env('WHATSAPP_TEMPLATE_ISOLIR', ''),
];