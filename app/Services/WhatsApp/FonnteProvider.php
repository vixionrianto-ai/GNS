<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FonnteProvider extends WhatsAppProvider
{
    protected string $endpoint;
    protected string $token;

    public function __construct()
    {
        $this->endpoint = (string) config('whatsapp.fonnte.endpoint', 'https://api.fonnte.com/send');
        $this->token = (string) config('whatsapp.fonnte.token', '');
    }

    public function send(string $nomor, string $pesan): bool
    {
        if ($this->token === '') {
            throw new RuntimeException('WHATSAPP_FONNTE_TOKEN belum diisi.');
        }

        $response = Http::timeout(20)
            ->withHeaders(['Authorization' => $this->token])
            ->asForm()
            ->post($this->endpoint, [
                'target' => $nomor,
                'message' => $pesan,
            ]);

        $this->response = $response->json() ?? $response->body();

        if (!$response->successful()) {
            return false;
        }

        $json = $response->json();
        if (is_array($json) && array_key_exists('status', $json)) {
            return filter_var($json['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($json['status'] === 'success');
        }

        return true;
    }

    public function sendDocument(string $nomor, string $pesan, string $documentUrl): bool
    {
        if ($this->token === '') {
            throw new RuntimeException('WHATSAPP_FONNTE_TOKEN belum diisi.');
        }

        $response = Http::timeout(30)
            ->withHeaders(['Authorization' => $this->token])
            ->asForm()
            ->post($this->endpoint, [
                'target' => $nomor,
                'message' => $pesan,
                'url' => $documentUrl,
            ]);

        $this->response = $response->json() ?? $response->body();

        if (!$response->successful()) {
            return false;
        }

        $json = $response->json();
        if (is_array($json) && array_key_exists('status', $json)) {
            return filter_var($json['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($json['status'] === 'success');
        }

        return true;
    }
}