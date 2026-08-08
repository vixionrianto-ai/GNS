<?php

namespace App\Services\WhatsApp;

interface WhatsAppProvider
{
    /**
     * Mengirim pesan WhatsApp.
     */
    public function send(
        string $phone,
        string $message
    ): bool;

    /**
     * Mengirim pesan WhatsApp dengan lampiran dokumen.
     */
    public function sendDocument(
        string $phone,
        string $message,
        string $documentUrl
    ): bool;
}