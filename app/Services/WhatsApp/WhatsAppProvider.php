<?php

namespace App\Services\WhatsApp;

abstract class WhatsAppProvider
{
    protected mixed $response = null;

    abstract public function send(string $nomor, string $pesan): bool;

    abstract public function sendDocument(string $nomor, string $pesan, string $documentUrl): bool;

    public function lastResponse(): mixed
    {
        return $this->response;
    }
}