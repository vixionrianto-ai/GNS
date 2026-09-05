<?php

namespace App\Services;

use App\Models\Router;
use App\Models\Pelanggan;
use App\Models\Paket;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Exception;

class MikroTikService
{
    protected array $clients = [];

    public function connect(Router $router): Client
    {
        $key = $router->id;
        if (isset($this->clients[$key])) return $this->clients[$key];

        $config = new Config([
            'host' => trim((string) $router->ip_router),
            'user' => (string) $router->username,
            'pass' => (string) $router->password,
            'port' => (int) $router->api_port,
            'ssl' => (bool) $router->ssl,
            'timeout' => 10,
            'socket_timeout' => 30,
            'attempts' => 2,
            'delay' => 1,
        ]);

        return $this->clients[$key] = new Client($config);
    }

    public function testConnection(Router $router): bool
    {
        try {
            return !empty($this->connect($router)->query(new Query('/system/resource/print'))->read());
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    public function testConnectionDetail(Router $router): array
    {
        try {
            $result = $this->connect($router)->query(new Query('/system/resource/print'))->read();
            $resource = $result[0] ?? [];
            return [
                'success' => true,
                'identity' => $this->getIdentity($router),
                'board' => $resource['board-name'] ?? null,
                'version' => $resource['version'] ?? null,
                'host' => $router->ip_router,
                'port' => (int) $router->api_port,
                'ssl' => (bool) $router->ssl,
                'message' => 'Berhasil terhubung ke MikroTik.',
            ];
        } catch (\Throwable $e) {
            report($e);
            return [
                'success' => false,
                'identity' => null,
                'board' => null,
                'version' => null,
                'host' => $router->ip_router,
                'port' => (int) $router->api_port,
                'ssl' => (bool) $router->ssl,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function readSecrets(Router $router): array
    {
        $query = new Query('/ppp/secret/print');
        $query->equal('.proplist', '.id,name,password,service,profile,disabled');
        return $this->connect($router)->query($query)->read();
    }

    /** Read-only PPP secret listing. */
    public function getSecrets(Router $router): array
    {
        return $this->readSecrets($router);
    }

    /** Explicit recovery operation for database customers missing a PPP secret. */
    public function getSecretsWithRecovery(Router $router): array
    {
        $secrets = $this->readSecrets($router);
        $byName = [];
        foreach ($secrets as $secret) {
            if (!empty($secret['name'])) $byName[$secret['name']] = $secret;
        }

        $pelanggans = Pelanggan::where('router_id', $router->id)
            ->whereNotNull('username_pppoe')
            ->where('username_pppoe', '!=', '')
            ->get();

        foreach ($pelanggans as $pelanggan) {
            $username = trim((string) $pelanggan->username_pppoe);
            if ($username === '' || isset($byName[$username])) continue;

            $password = (string) ($pelanggan->password_pppoe ?? '');
            if ($password === '') continue;

            $paket = $pelanggan->paket_id ? Paket::find($pelanggan->paket_id) : null;
            $profile = trim((string) ($paket?->profile_mikrotik ?? ''));
            if ($profile === '') continue;

            try {
                $query = new Query('/ppp/secret/add');
                $query->equal('name', $username);
                $query->equal('password', $password);
                $query->equal('profile', $profile);
                $query->equal('service', 'pppoe');
                $this->connect($router)->query($query)->read();

                $created = null;
                for ($i = 0; $i < 5; $i++) {
                    usleep(200000);
                    foreach ($this->readSecrets($router) as $secret) {
                        if (($secret['name'] ?? '') === $username) {
                            $created = $secret;
                            break 2;
                        }
                    }
                }

                if ($created) {
                    $pelanggan->mikrotik_secret_id = $created['.id'] ?? null;
                    $pelanggan->save();
                    $byName[$username] = $created;

                    if (($pelanggan->status ?? '') === 'Aktif') {
                        $this->enableSecretById($router, $created['.id']);
                    } else {
                        $this->disableSecretById($router, $created['.id']);
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $this->readSecrets($router);
    }

    public function getSecretByName(Router $router, string $username): ?array
    {
        foreach ($this->readSecrets($router) as $secret) {
            if (($secret['name'] ?? '') === $username) return $secret;
        }
        return null;
    }

    public function getSecretById(Router $router, string $id): ?array
    {
        foreach ($this->readSecrets($router) as $secret) {
            if (($secret['.id'] ?? '') === $id) return $secret;
        }
        return null;
    }

    public function createSecret(Router $router, string $username, string $password, string $profile, string $service = 'pppoe'): string
    {
        $client = $this->connect($router);
        if ($this->getSecretByName($router, $username)) {
            throw new Exception("PPP Secret {$username} sudah ada.");
        }
        $query = new Query('/ppp/secret/add');
        $query->equal('name', $username);
        $query->equal('password', $password);
        $query->equal('profile', $profile);
        $query->equal('service', $service);
        $client->query($query)->read();

        for ($i = 0; $i < 5; $i++) {
            usleep(200000);
            $secret = $this->getSecretByName($router, $username);
            if ($secret) return $secret['.id'];
        }
        throw new Exception('PPP Secret berhasil dibuat tetapi ID MikroTik tidak ditemukan.');
    }

    public function updateSecretById(Router $router, string $id, string $username, string $password, string $profile, string $service = 'pppoe'): bool
    {
        $query = new Query('/ppp/secret/set');
        $query->equal('.id', $id);
        $query->equal('name', $username);
        $query->equal('password', $password);
        $query->equal('profile', $profile);
        $query->equal('service', $service);
        $this->connect($router)->query($query)->read();
        return true;
    }

    public function deleteSecretById(Router $router, string $id): bool
    {
        $query = new Query('/ppp/secret/remove');
        $query->equal('.id', $id);
        $this->connect($router)->query($query)->read();
        return true;
    }

    public function enableSecretById(Router $router, string $id): bool
    {
        $query = new Query('/ppp/secret/enable');
        $query->equal('.id', $id);
        $this->connect($router)->query($query)->read();
        return true;
    }

    public function disableSecretById(Router $router, string $id): bool
    {
        $query = new Query('/ppp/secret/disable');
        $query->equal('.id', $id);
        $this->connect($router)->query($query)->read();
        return true;
    }

    public function getActiveSessions(Router $router): array
    {
        $query = new Query('/ppp/active/print');
        $query->equal('.proplist', '.id,name,address,caller-id,uptime,service');
        return $this->connect($router)->query($query)->read();
    }

    public function getActiveByUsername(Router $router, string $username): ?array
    {
        foreach ($this->getActiveSessions($router) as $active) {
            if (($active['name'] ?? '') === $username) return $active;
        }
        return null;
    }

    public function isOnline(Router $router, string $username): bool
    {
        return $this->getActiveByUsername($router, $username) !== null;
    }

    public function disconnectActiveSession(Router $router, string $username): bool
    {
        $active = $this->getActiveByUsername($router, $username);
        if (!$active) return false;
        $query = new Query('/ppp/active/remove');
        $query->equal('.id', $active['.id']);
        $this->connect($router)->query($query)->read();
        return true;
    }

    public function disconnectActiveSessionBySecretId(Router $router, string $secretId): bool
    {
        $secret = $this->getSecretById($router, $secretId);
        return $secret ? $this->disconnectActiveSession($router, $secret['name']) : false;
    }

    public function disconnectAllSessions(Router $router, string $username): int
    {
        $count = 0;
        foreach ($this->getActiveSessions($router) as $active) {
            if (($active['name'] ?? '') !== $username) continue;
            $query = new Query('/ppp/active/remove');
            $query->equal('.id', $active['.id']);
            $this->connect($router)->query($query)->read();
            $count++;
        }
        return $count;
    }

    public function getProfiles(Router $router): array
    {
        $query = new Query('/ppp/profile/print');
        $query->equal('.proplist', '.id,name,local-address,remote-address,rate-limit,only-one');
        return $this->connect($router)->query($query)->read();
    }

    public function getProfileNames(Router $router): array
    {
        $profiles = [];
        foreach ($this->getProfiles($router) as $profile) {
            if (!empty($profile['name'])) $profiles[] = $profile['name'];
        }
        sort($profiles);
        return $profiles;
    }

    public function secretExists(Router $router, string $username): bool
    {
        return $this->getSecretByName($router, $username) !== null;
    }

    public function getSecretId(Router $router, string $username): ?string
    {
        $secret = $this->getSecretByName($router, $username);
        return $secret['.id'] ?? null;
    }

    public function getIdentity(Router $router): ?string
    {
        $result = $this->connect($router)->query(new Query('/system/identity/print'))->read();
        return $result[0]['name'] ?? null;
    }

    public function getRouterVersion(Router $router): ?string
    {
        $result = $this->connect($router)->query(new Query('/system/resource/print'))->read();
        return $result[0]['version'] ?? null;
    }

    public function getBoardName(Router $router): ?string
    {
        $result = $this->connect($router)->query(new Query('/system/resource/print'))->read();
        return $result[0]['board-name'] ?? null;
    }

    public function getUptime(Router $router): ?string
    {
        $result = $this->connect($router)->query(new Query('/system/resource/print'))->read();
        return $result[0]['uptime'] ?? null;
    }
}
