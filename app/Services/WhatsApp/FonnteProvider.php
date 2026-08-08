<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;

class FonnteProvider implements WhatsAppProvider
{
    protected string $token;

    /**
     * Response terakhir dari provider.
     */
    protected ?array $lastResponse = null;

    public function __construct()
    {
        $this->token = config('services.fonnte.token');
    }

    public function send(
        string $phone,
        string $message
    ): bool {

                $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->post(
            'https://api.fonnte.com/send',
            [
                'target'  => $phone,
                'message' => $message,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Simpan response asli Fonnte
        |--------------------------------------------------------------------------
        */

        \Log::info('Fonnte Response', [

            'http_status' => $response->status(),

            'json' => $response->json(),

            'body' => $response->body(),

        ]);
        $this->lastResponse = [

            'http_status' => $response->status(),

            'body' => $response->body(),

            'json' => $response->json(),

        ];
        if (! $response->successful()) {

            \Log::error('Fonnte HTTP Error', [

                'status' => $response->status(),

                'body' => $response->body(),

            ]);

            return false;

        }

        $json = $response->json();

        if (isset($json['status']) && $json['status'] === false) {

            \Log::error('Fonnte API Error', $json);

            return false;

        }

                return true;
    }

    /**
     * Mengirim WhatsApp dengan lampiran dokumen.
     */
    public function sendDocument(
        string $phone,
        string $message,
        string $documentUrl
    ): bool {

        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->post(
            'https://api.fonnte.com/send',
            [
                'target'      => $phone,
                'message'     => $message,
                'url'         => $documentUrl,
                'filename'    => 'Invoice.pdf',
                'countryCode' => '62',
            ]
        );

        \Log::info('Fonnte Document Response', [

            'http_status' => $response->status(),

            'json' => $response->json(),

            'body' => $response->body(),

            'document' => $documentUrl,

        ]);
        $this->lastResponse = [

            'http_status' => $response->status(),

            'body' => $response->body(),

            'json' => $response->json(),

        ];
        if (! $response->successful()) {

            \Log::error('Fonnte Document HTTP Error', [

                'status' => $response->status(),

                'body' => $response->body(),

            ]);

            return false;

        }

        $json = $response->json();

        if (isset($json['status']) && $json['status'] === false) {

            \Log::error('Fonnte Document API Error', $json);

            return false;

        }

        return true;
    }
        
    /**
     * Mengambil response terakhir dari provider.
     */
    public function lastResponse(): ?array
    {
        return $this->lastResponse;
    }
}