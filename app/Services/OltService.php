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
    public function findByUsername(Router $router, string $username, ?string $callerId = null): ?array
    {
        $oltConfig = $this->configForRouter($router);

        if (!$this->enabled($router)) {
            Log::warning('OLT lookup dilewati: konfigurasi OLT belum aktif.', [
                'username' => $username,
                'enabled' => (bool) config('services.olt.enabled', false),
                'base_url_filled' => filled($oltConfig['base_url'] ?? null),
                'username_filled' => filled($oltConfig['username'] ?? null),
                'password_filled' => filled($oltConfig['password'] ?? null),
            ]);
            return null;
        }

        if (trim($username) === '' && trim((string) $callerId) === '') {
            return null;
        }

        try {
            $jar = new CookieJar();

            $client = new Client([
                'timeout' => (float) config('services.olt.timeout', 10),
                'connect_timeout' => (float) config('services.olt.timeout', 10),
                'http_errors' => false,
                'cookies' => $jar,
            ]);

            Log::info('OLT lookup mulai.', [
                'username' => $username,
                'enabled' => $this->enabled($router),
                'router' => $router->nama_router,
                'base_url' => $oltConfig['base_url'] ?? null,
                'base_url_filled' => filled($oltConfig['base_url'] ?? null),
                'username_filled' => filled($oltConfig['username'] ?? null),
                'password_filled' => filled($oltConfig['password'] ?? null),
            ]);

            $base = rtrim((string) ($oltConfig['base_url'] ?? ''), '/');
            $loginPage = $base . '/action/login.html';
            $loginUrl = $base . '/action/main.html';
            $statusUrl = $base . '/action/onustatusinfo.html';
            $opmUrl = $base . '/action/onuopmdiag.html';

            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/152',
            ];

            $loginResponse = $client->get($loginPage, [
                'headers' => $headers,
            ]);

            Log::info('OLT login page response.', [
                'status' => $loginResponse->getStatusCode(),
            ]);

            $loginResponse = $client->post($loginUrl, [
                'headers' => $headers + [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer' => $loginPage,
                ],
                'form_params' => [
                    'user' => (string) ($oltConfig['username'] ?? ''),
                    'pass' => (string) ($oltConfig['password'] ?? ''),
                    'button' => 'login',
                    'who' => '100',
                ],
            ]);

            Log::info('OLT login response.', [
                'status' => $loginResponse->getStatusCode(),
            ]);

            Log::info('OLT login selesai, mulai baca PON 1-4.', [
                'base_url' => $base,
            ]);

            for ($pon = 1; $pon <= 4; $pon++) {
                $statusHtml = $this->requestPage($client, $jar, $statusUrl, [
                    'select' => (string) $pon,
                    'searchMac' => '',
                    'searchDescription' => '',
                    'who' => '100',
                ], $statusUrl);

                $statusRows = $this->parseStatus($statusHtml);

                Log::info('OLT status dibaca.', [
                    'pon' => $pon,
                    'rows' => count($statusRows),
                    'target' => $username,
                ]);

                // Jika halaman status berhasil dibaca, cocokkan langsung.
                $matchedRow = $this->matchOnuRow($statusRows, $username, $callerId);

                // Fallback: cocokkan dari halaman OPM juga. Ini penting karena
                // data OLT yang kita miliki terbukti memuat MAC + Description
                // yang sama dengan PPP Caller-ID/username.
                if (!$matchedRow) {
                    $opmHtml = $this->requestPage($client, $jar, $opmUrl, [
                        'select' => (string) $pon,
                        'searchMac' => '',
                        'searchDescription' => '',
                        'who' => '100',
                    ], $opmUrl);

                    $opmRows = $this->parseOpm($opmHtml);

                    Log::info('OLT OPM fallback dibaca.', [
                        'pon' => $pon,
                        'rows' => count($opmRows),
                        'target' => $username,
                    ]);

                    $matchedOpm = $this->matchOnuRow($opmRows, $username, $callerId);

                    if ($matchedOpm) {
                        $matchedRow = [
                            'onu' => $matchedOpm['onu'],
                            'status' => 'Unknown',
                            'mac' => $matchedOpm['mac'] ?? '',
                            'description' => $matchedOpm['description'] ?? '',
                            'distance' => $matchedOpm['distance'] ?? '-',
                            'last_deregister_reason' => '-',
                        ];
                    } else {
                        continue;
                    }
                } else {
                    $opmHtml = $this->requestPage($client, $jar, $opmUrl, [
                        'select' => (string) $pon,
                        'searchMac' => '',
                        'searchDescription' => '',
                        'who' => '100',
                    ], $opmUrl);

                    $opmRows = $this->parseOpm($opmHtml);

                    Log::info('OLT OPM dibaca.', [
                        'pon' => $pon,
                        'rows' => count($opmRows),
                        'target' => $username,
                    ]);
                }

                $opm = collect($opmRows)->firstWhere('onu', $matchedRow['onu']);

                Log::info('OLT ONU cocok.', [
                    'pon' => $pon,
                    'onu' => $matchedRow['onu'],
                    'description' => $matchedRow['description'],
                    'rx_power' => $opm['rx_power'] ?? null,
                    'tx_power' => $opm['tx_power'] ?? null,
                    'distance' => $matchedRow['distance'] ?? null,
                ]);

                return [
                    'onu' => $matchedRow['onu'],
                    'status' => $matchedRow['status'],
                    'mac' => $matchedRow['mac'],
                    'description' => $matchedRow['description'],
                    'distance' => $matchedRow['distance'],
                    'last_deregister_reason' => $matchedRow['last_deregister_reason'],
                    'temperature' => $opm['temperature'] ?? null,
                    'voltage' => $opm['voltage'] ?? null,
                    'tx_bias' => $opm['tx_bias'] ?? null,
                    'tx_power' => $opm['tx_power'] ?? null,
                    'rx_power' => $opm['rx_power'] ?? null,
                ];
            }

            Log::warning('OLT ONU tidak ditemukan pada seluruh PON.', [
                'username' => $username,
                'caller_id' => $callerId,
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal mengambil data ONU dari OLT.', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function enabled(?Router $router = null): bool
    {
        $config = $router ? $this->configForRouter($router) : config('services.olt');

        return (bool) config('services.olt.enabled', false)
            && filled($config['base_url'] ?? null)
            && filled($config['username'] ?? null)
            && filled($config['password'] ?? null);
    }

    protected function configForRouter(Router $router): array
    {
        $global = config('services.olt', []);
        $routers = config('services.olt.routers', []);

        $key = strtoupper(trim((string) $router->nama_router));
        $specific = is_array($routers[$key] ?? null) ? $routers[$key] : [];

        return array_merge([
            'base_url' => $global['base_url'] ?? null,
            'username' => $global['username'] ?? null,
            'password' => $global['password'] ?? null,
        ], $specific);
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
        $patterns = [
            '/name=[\'\"]SessionKey[\'\"][^>]*value=[\'\"]([^\'\"]*)[\'\"]/i',
            '/value=[\'\"]([^\'\"]*)[\'\"][^>]*name=[\'\"]SessionKey[\'\"]/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match)) {
                return trim(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        return '';
    }

    protected function parseStatus(string $html): array
    {
        $rows = [];

        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $matches);

        foreach ($matches[1] ?? [] as $rowHtml) {
            preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $rowHtml, $cells);

            if (count($cells[1] ?? []) < 5) {
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
                'last_deregister_reason' => $cells[8] ?? '-',
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

    protected function matchOnuRow(array $rows, string $username, ?string $callerId): ?array
    {
        $target = trim($username);
        $targetMac = $this->normalizeMac($callerId);

        foreach ($rows as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            $rowMac = $this->normalizeMac($row['mac'] ?? '');

            if ($target !== '' && strcasecmp($description, $target) === 0) {
                return $row;
            }

            if ($target !== '' && stripos($description, $target) !== false) {
                return $row;
            }

            if ($targetMac !== '' && $rowMac !== '' && $targetMac === $rowMac) {
                return $row;
            }
        }

        return null;
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
