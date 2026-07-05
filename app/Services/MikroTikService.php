<?php

namespace App\Services;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Exception;

class MikroTikService

{
    /**
     * Cache koneksi RouterOS
     */
    protected array $clients = [];

    /*
    |--------------------------------------------------------------------------
    | CONNECT
    |--------------------------------------------------------------------------
    */

    public function connect(
        Router $router
    ): Client
    {
        $key = $router->id;

        if (isset($this->clients[$key])) {

            return $this->clients[$key];

        }

        $config = new Config([

            'host' => $router->ip_router,

            'user' => $router->username,

            'pass' => $router->password,

            'port' => $router->api_port,

            // timeout lebih panjang
            'timeout' => 20,

        ]);

        $this->clients[$key] = new Client($config);

        return $this->clients[$key];
    }

    /*
    |--------------------------------------------------------------------------
    | TEST CONNECTION
    |--------------------------------------------------------------------------
    */

    public function testConnection(
        Router $router
    ): bool
    {
        try {

            $client = $this->connect($router);

            $query = new Query(
                '/system/identity/print'
            );

            $client
                ->query($query)
                ->read();

            return true;

        } catch (\Throwable $e) {

            return false;

        }
    }

    /*
    |--------------------------------------------------------------------------
    | PPP SECRET
    |--------------------------------------------------------------------------
    */

    public function getSecrets(
        Router $router
    ): array
    {
        $client = $this->connect($router);

        $query = new Query(
            '/ppp/secret/print'
        );

        $query->equal(

            '.proplist',

            '.id,name,password,service,profile,disabled'

        );

        return $client
            ->query($query)
            ->read();
    }

    public function getSecretByName(
        Router $router,
        string $username
    ): ?array
    {
        foreach (
            $this->getSecrets($router)
            as $secret
        ) {

            if (
                ($secret['name'] ?? '')
                === $username
            ) {

                return $secret;

            }

        }

        return null;
    }

    public function getSecretById(
        Router $router,
        string $id
    ): ?array
    {
        foreach (
            $this->getSecrets($router)
            as $secret
        ) {

            if (
                ($secret['.id'] ?? '')
                === $id
            ) {

                return $secret;

            }

        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PPP SECRET
    |--------------------------------------------------------------------------
    */

    public function createSecret(
        Router $router,
        string $username,
        string $password,
        string $profile,
        string $service = 'pppoe'
    ): string
    {
        $client = $this->connect(
            $router
        );

        if (
            $this->getSecretByName(
                $router,
                $username
            )
        ) {

            throw new Exception(
                "PPP Secret {$username} sudah ada."
            );

        }

        $query = new Query(
            '/ppp/secret/add'
        );

        $query->equal(
            'name',
            $username
        );

        $query->equal(
            'password',
            $password
        );

        $query->equal(
            'profile',
            $profile
        );

        $query->equal(
            'service',
            $service
        );

        $client
            ->query($query)
            ->read();

        /*
        |--------------------------------------------------------------------------
        | AMBIL ULANG PPP SECRET
        |--------------------------------------------------------------------------
        */

        $secret = null;

        for (
            $i = 0;
            $i < 5;
            $i++
        ) {

            usleep(200000);

            $secret =
                $this->getSecretByName(
                    $router,
                    $username
                );

            if ($secret) {
                break;
            }

        }

        if (!$secret) {

            throw new Exception(
                'PPP Secret berhasil dibuat tetapi ID MikroTik tidak ditemukan.'
            );

        }

        return $secret['.id'];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PPP SECRET
    |--------------------------------------------------------------------------
    */

        /**
     * Update PPP Secret berdasarkan ID MikroTik
     */
    public function updateSecretById(
        Router $router,
        string $id,
        string $username,
        string $password,
        string $profile,
        string $service = 'pppoe'
    ): bool
    {
        $client = $this->connect($router);

        $query = new Query('/ppp/secret/set');

        $query->equal('.id', $id);
        $query->equal('name', $username);
        $query->equal('password', $password);
        $query->equal('profile', $profile);
        $query->equal('service', $service);

        $client
            ->query($query)
            ->read();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE PPP SECRET
    |--------------------------------------------------------------------------
    */

    public function deleteSecretById(
        Router $router,
        string $id
    ): bool
    {
        $client = $this->connect($router);

        $query = new Query('/ppp/secret/remove');

        $query->equal('.id', $id);

        $client
            ->query($query)
            ->read();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | ENABLE PPP SECRET
    |--------------------------------------------------------------------------
    */

    public function enableSecretById(
        Router $router,
        string $id
    ): bool
    {
        $client = $this->connect($router);

        $query = new Query('/ppp/secret/enable');

        $query->equal('.id', $id);

        $client
            ->query($query)
            ->read();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | DISABLE PPP SECRET
    |--------------------------------------------------------------------------
    */

    public function disableSecretById(
        Router $router,
        string $id
    ): bool
    {
        $client = $this->connect($router);

        $query = new Query('/ppp/secret/disable');

        $query->equal('.id', $id);

        $client
            ->query($query)
            ->read();

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | PPP ACTIVE
    |--------------------------------------------------------------------------
    */
        /**
     * Mengambil seluruh PPP Active
     */
    public function getActiveSessions(
        Router $router
    ): array
    {
        $client = $this->connect($router);

        $query = new Query('/ppp/active/print');

        $query->equal(
            '.proplist',
            '.id,name,address,caller-id,uptime,service'
        );

        return $client
            ->query($query)
            ->read();
    }

    /**
     * Mengambil PPP Active berdasarkan username
     */
    public function getActiveByUsername(
        Router $router,
        string $username
    ): ?array
    {
        foreach (
            $this->getActiveSessions($router)
            as $active
        ) {

            if (
                ($active['name'] ?? '') === $username
            ) {

                return $active;

            }

        }

        return null;
    }

    /**
     * Mengecek apakah pelanggan sedang online
     */
    public function isOnline(
        Router $router,
        string $username
    ): bool
    {
        return $this->getActiveByUsername(
            $router,
            $username
        ) !== null;
    }

    /**
     * Disconnect 1 sesi PPP Active
     */
    public function disconnectActiveSession(
        Router $router,
        string $username
    ): bool
    
    {
        $client = $this->connect($router);

        $active = $this->getActiveByUsername(
            $router,
            $username
        );

        if (!$active) {
            return false;
        }

        $query = new Query('/ppp/active/remove');

        $query->equal(
            '.id',
            $active['.id']
        );

        $client
            ->query($query)
            ->read();

        return true;
    }
    /**
     * Disconnect berdasarkan Secret ID
     */
    public function disconnectActiveSessionBySecretId(
        Router $router,
        string $secretId
    ): bool
    {
        $secret = $this->getSecretById(
            $router,
            $secretId
        );

        if (!$secret) {
            return false;
        }

        return $this->disconnectActiveSession(
            $router,
            $secret['name']
        );
    }

    /**
     * Disconnect seluruh sesi milik username
     */
    public function disconnectAllSessions(
        Router $router,
        string $username
    ): int
    {
        $client = $this->connect($router);

        $count = 0;

        foreach (
            $this->getActiveSessions($router)
            as $active
        ) {

            if (
                ($active['name'] ?? '')
                !== $username
            ) {
                continue;
            }

            $query = new Query(
                '/ppp/active/remove'
            );

            $query->equal(
                '.id',
                $active['.id']
            );

            $client
                ->query($query)
                ->read();

            $count++;
        }

        return $count;
    }

    /*
    |--------------------------------------------------------------------------
    | PPP PROFILE
    |--------------------------------------------------------------------------
    */

        /**
     * Mengambil seluruh PPP Profile
     */
    public function getProfiles(
        Router $router
    ): array
    {
        $client = $this->connect($router);

        $query = new Query('/ppp/profile/print');

        $query->equal(
            '.proplist',
            '.id,name,local-address,remote-address,rate-limit,only-one'
        );

        return $client
            ->query($query)
            ->read();
    }

    /**
     * Mengambil daftar nama Profile
     */
    public function getProfileNames(
        Router $router
    ): array
    {
        $profiles = [];

        foreach (
            $this->getProfiles($router)
            as $profile
        ) {

            if (!empty($profile['name'])) {

                $profiles[] =
                    $profile['name'];

            }

        }

        sort($profiles);

        return $profiles;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER
    |--------------------------------------------------------------------------
    */

    /**
     * Mengecek apakah PPP Secret ada
     */
    public function secretExists(
        Router $router,
        string $username
    ): bool
    {
        return $this->getSecretByName(
            $router,
            $username
        ) !== null;
    }

    /**
     * Mengambil ID MikroTik berdasarkan username
     */
    public function getSecretId(
        Router $router,
        string $username
    ): ?string
    {
        $secret = $this->getSecretByName(
            $router,
            $username
        );

        return $secret['.id'] ?? null;
    }

    /**
     * Mengambil Identity Router
     */
    public function getIdentity(
        Router $router
    ): ?string
    {
        $client = $this->connect($router);

        $query = new Query('/system/identity/print');

        $result = $client
            ->query($query)
            ->read();

        return $result[0]['name'] ?? null;
    }

    /**
     * Mengambil RouterOS Version
     */
    public function getRouterVersion(
        Router $router
    ): ?string
    {
        $client = $this->connect($router);

        $query = new Query('/system/resource/print');

        $result = $client
            ->query($query)
            ->read();

        return $result[0]['version'] ?? null;
    }

    /**
     * Mengambil RouterOS Board
     */
    public function getBoardName(
        Router $router
    ): ?string
    {
        $client = $this->connect($router);

        $query = new Query('/system/resource/print');

        $result = $client
            ->query($query)
            ->read();

        return $result[0]['board-name'] ?? null;
    }

    /**
     * Mengambil Uptime Router
     */
    public function getUptime(
        Router $router
    ): ?string
    {
        $client = $this->connect($router);

        $query = new Query('/system/resource/print');

        $result = $client
            ->query($query)
            ->read();

        return $result[0]['uptime'] ?? null;
    }
}