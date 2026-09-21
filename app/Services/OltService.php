<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Throwable;

class OltService
{
    /**
     * Cari data ONU berdasarkan username PPP yang tersimpan
     * pada kolom Description di OLT.
     */
    public function findByUsername(string $username, ?string $callerId = null): ?array
    {
        if (!$this->enabled() || (trim($username) === '' && trim((string) $callerId) === '')) {
            return null;
        }

        try {
            $client = new Client([
                'timeout' => (float) config('services.olt.timeout', 10),
                'http_errors' => false,
                'cookies' => true,
            ]);

            $jar = new CookieJar();

            $base = rtrim((string) config('services.olt.base_url'), '/');
            $loginPage = $base . '/action/login.html';
            $loginUrl = $base . '/action/main.html';
            $statusUrl = $base . '/action/onustatusinfo.html';
            $opmUrl = $base . '/action/onuopmdiag.html';

            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152',
            ];

            $client->get($loginPage, [
                'headers' => $headers,
                'cookies' => $jar,
            ]);

            $client->post($loginUrl, [
                'headers' => $headers + [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer' => $loginPage,
                ],
                'form_params' => [
                    'user' => (string) config('services.olt.username'),
                    'pass' => (string) config('services.olt.password'),
                    'button' => 'login',
                    'who' => '100',
                ],
                'cookies' => $jar,
            ]);

            for ($pon = 1; $pon <= 4; $pon++) {
                $statusHtml = $this->requestPage($client, $jar, $statusUrl, [
                    'select' => (string) $pon,
                    'searchMac' => '',
                    'searchDescription' => '',
                    'who' => '100',
                ], $statusUrl);

                $statusRows = $this->parseStatus($statusHtml);

                foreach ($statusRows as $row) {
                    $description = trim($row['description']);
                    $target = trim($username);
                    $targetMac = $this->normalizeMac($callerId);
                    $rowMac = $this->normalizeMac($row['mac'] ?? '');

                    // Prioritas 1: username PPP di Description.
                    $matched = $target !== '' && strcasecmp($description, $target) === 0;

                    if (!$matched && $target !== '') {
                        $matched = stripos($description, $target) !== false;
                    }

                    // Prioritas 2: Caller-ID PPPoE sering merupakan MAC ONU.
                    // Ini dipakai sebagai fallback jika Description OLT tidak
                    // berisi username PPP.
                    if (!$matched && $targetMac !== '' && $rowMac !== '') {
                        $matched = $targetMac === $rowMac;
                    }

                    if ($matched) {
                        $opmHtml = $this->requestPage($client, $jar, $opmUrl, [
                            'select' => (string) $pon,
                            'searchMac' => '',
                            'searchDescription' => '',
                            'who' => '100',
                        ], $opmUrl);

                        $opmRows = $this->parseOpm($opmHtml);
                        $opm = collect($opmRows)->firstWhere('onu', $row['onu']);

                        // Beberapa halaman OLT mengembalikan OPM dengan
                        // format ONU yang sedikit berbeda. Jika exact ID
                        // belum ketemu, cocokkan nomor ONU di bagian akhir.
                        if (!$opm) {
                            $rowOnu = trim((string) $row['onu']);
                            foreach ($opmRows as $candidate) {
                                $candidateOnu = trim((string) ($candidate['onu'] ?? ''));
                                if ($candidateOnu !== '' && $candidateOnu === $rowOnu) {
                                    $opm = $candidate;
                                    break;
                                }
                            }
                        }

                        return [
                            'onu' => $row['onu'],
                            'status' => $row['status'],
                            'mac' => $row['mac'],
                            'description' => $row['description'],
                            'distance' => $row['distance'],
                            'last_deregister_reason' => $row['last_deregister_reason'],
                            'temperature' => $opm['temperature'] ?? null,
                            'voltage' => $opm['voltage'] ?? null,
                            'tx_bias' => $opm['tx_bias'] ?? null,
                            'tx_power' => $opm['tx_power'] ?? null,
                            'rx_power' => $opm['rx_power'] ?? null,
                        ];
                    }
                }
            }
        } catch (Throwable $e) {
            Log::warning('Gagal mengambil data ONU dari OLT.', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function enabled(): bool
    {
        return (bool) config('services.olt.enabled', false)
            && filled(config('services.olt.base_url'))
            && filled(config('services.olt.username'))
            && filled(config('services.olt.password'));
    }

    protected function requestPage(Client $client, CookieJar $jar, string $url, array $data, string $referer): string
    {
        $response = $client->get($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152',
                'Referer' => $referer,
            ],
            'cookies' => $jar,
        ]);

        $html = mb_convert_encoding((string) $response->getBody(), 'UTF-8', 'GB2312');

        $sessionKey = $this->sessionKey($html);

        if ($sessionKey !== '') {
            $data['SessionKey'] = $sessionKey;
        }

        $response = $client->post($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152',
                'Referer' => $referer,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => $data,
            'cookies' => $jar,
        ]);

        return mb_convert_encoding((string) $response->getBody(), 'UTF-8', 'GB2312');
    }

    protected function sessionKey(string $html): string
    {
        if (preg_match('/name=[\'\"]SessionKey[\'\"][^>]*value=[\'\"]([^\'\"]*)[\'\"]/i', $html, $match)) {
            return $match[1];
        }

        return '';
    }

    protected function parseStatus(string $html): array
    {
        $rows = [];

        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $matches);

        foreach ($matches[1] ?? [] as $rowHtml) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $rowHtml, $cells);

            if (count($cells[1] ?? []) < 9) {
                continue;
            }

            $cells = array_map(fn ($cell) => $this->clean($cell), $cells[1]);

            if (!str_starts_with($cells[0], 'EPON')) {
                continue;
            }

            $rows[] = [
                'onu' => $cells[0],
                'status' => $cells[1],
                'mac' => $cells[2],
                'description' => $cells[3],
                'distance' => $cells[4],
                'last_deregister_reason' => $cells[8],
            ];
        }

        return $rows;
    }

    protected function parseOpm(string $html): array
    {
        $rows = [];

        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $matches);

        foreach ($matches[1] ?? [] as $rowHtml) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $rowHtml, $cells);

            if (count($cells[1] ?? []) < 9) {
                continue;
            }

            $cells = array_map(fn ($cell) => $this->clean($cell), $cells[1]);

            if (!str_starts_with($cells[0], 'EPON')) {
                continue;
            }

            $rows[] = [
                'onu' => $cells[0],
                'mac' => $cells[1],
                'description' => $cells[2],
                'distance' => $cells[3],
                'temperature' => $cells[4],
                'voltage' => $cells[5],
                'tx_bias' => $cells[6],
                'tx_power' => $cells[7],
                'rx_power' => $cells[8],
            ];
        }

        return $rows;
    }

    protected function normalizeMac(?string $mac): string
    {
        return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', (string) $mac) ?? '');
    }

    protected function clean(string $cell): string
    {
        $cell = strip_tags($cell);
        $cell = html_entity_decode($cell, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cell = preg_replace('/\s+/', ' ', $cell) ?? $cell;

        return trim($cell);
    }
}
