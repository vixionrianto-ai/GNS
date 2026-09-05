<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\MikroTikService;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function __construct(protected MikroTikService $mikrotik)
    {
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Router::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_router' => ['required', 'string', 'max:100'],
            'ip_router' => ['required', 'string', 'max:255'],
            'api_port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'versi_routeros' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $data['ssl'] = $request->boolean('ssl');
        $router = Router::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Router berhasil ditambahkan.',
            'data' => $router,
        ], 201);
    }

    public function show(Router $router)
    {
        return response()->json([
            'success' => true,
            'data' => $router,
        ]);
    }

    public function update(Request $request, Router $router)
    {
        $data = $request->validate([
            'nama_router' => ['required', 'string', 'max:100'],
            'ip_router' => ['required', 'string', 'max:255'],
            'api_port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'versi_routeros' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $data['ssl'] = $request->boolean('ssl');
        $router->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Router berhasil diperbarui.',
            'data' => $router->fresh(),
        ]);
    }

    public function destroy(Router $router)
    {
        if ($router->pelanggans()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Router tidak dapat dihapus karena masih digunakan pelanggan.',
            ], 422);
        }

        $router->delete();

        return response()->json([
            'success' => true,
            'message' => 'Router berhasil dihapus.',
        ]);
    }

    public function test(Router $router)
    {
        $result = $this->mikrotik->testConnectionDetail($router);
        $status = $result['success'] ? 200 : 422;

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? 'Berhasil terhubung : '
                    . ($result['board'] ?? 'Router MikroTik')
                    . ' | RouterOS '
                    . ($result['version'] ?? '-')
                : 'Koneksi MikroTik gagal: ' . $result['message'],
            'data' => $result['success'] ? [
                'identity' => $result['identity'],
                'board' => $result['board'],
                'version' => $result['version'],
                'host' => $result['host'],
                'port' => $result['port'],
                'ssl' => $result['ssl'],
            ] : null,
        ], $status);
    }

    public function pppSecret(Router $router)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->mikrotik->getSecrets($router),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeSecret(Request $request, Router $router)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:50'],
            'profile' => ['required', 'string', 'max:100'],
        ]);

        try {
            $secretId = $this->mikrotik->createSecret(
                $router,
                $data['username'],
                $data['password'],
                $data['profile'],
                $data['service']
            );

            return response()->json([
                'success' => true,
                'message' => 'PPP Secret berhasil ditambahkan.',
                'data' => ['id' => $secretId],
            ], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function editSecret(Router $router, string $username)
    {
        try {
            $secret = $this->mikrotik->getSecretByName($router, $username);
            if (!$secret) {
                return response()->json(['success' => false, 'message' => 'PPP Secret tidak ditemukan.'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'secret' => $secret,
                    'profiles' => $this->mikrotik->getProfileNames($router),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSecret(Request $request, Router $router, string $secret)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:50'],
            'profile' => ['required', 'string', 'max:100'],
            'disabled' => ['required', 'string', 'in:yes,no'],
        ]);

        try {
            if (!$this->mikrotik->getSecretById($router, $secret)) {
                return response()->json(['success' => false, 'message' => 'PPP Secret tidak ditemukan.'], 404);
            }

            $this->mikrotik->updateSecretById(
                $router,
                $secret,
                $data['username'],
                $data['password'] ?? null,
                $data['profile'],
                $data['service'],
                $data['disabled']
            );

            return response()->json(['success' => true, 'message' => 'PPP Secret berhasil diupdate.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteSecret(Router $router, string $secret)
    {
        try {
            if (!$this->mikrotik->getSecretById($router, $secret)) {
                return response()->json(['success' => false, 'message' => 'PPP Secret tidak ditemukan.'], 404);
            }

            $this->mikrotik->deleteSecretById($router, $secret);

            return response()->json(['success' => true, 'message' => 'PPP Secret berhasil dihapus.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function enableSecret(Router $router, string $secret)
    {
        return $this->setSecretState($router, $secret, true);
    }

    public function disableSecret(Router $router, string $secret)
    {
        return $this->setSecretState($router, $secret, false);
    }

    public function pppProfile(Router $router)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->mikrotik->getProfiles($router),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function storeProfile(Request $request, Router $router)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'local_address' => ['nullable', 'string', 'max:255'],
            'remote_address' => ['nullable', 'string', 'max:255'],
            'rate_limit' => ['nullable', 'string', 'max:255'],
            'only_one' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $this->mikrotik->createProfile(
                $router,
                $data['name'],
                $data['local_address'] ?? null,
                $data['remote_address'] ?? null,
                $data['rate_limit'] ?? null,
                $data['only_one'] ?? null
            );

            return response()->json(['success' => true, 'message' => 'PPP Profile berhasil ditambahkan.'], 201);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function editProfile(Router $router, string $profile)
    {
        try {
            $data = $this->mikrotik->getProfileById($router, $profile);
            if (!$data) {
                return response()->json(['success' => false, 'message' => 'PPP Profile tidak ditemukan.'], 404);
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request, Router $router, string $profile)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'local_address' => ['nullable', 'string', 'max:255'],
            'remote_address' => ['nullable', 'string', 'max:255'],
            'rate_limit' => ['nullable', 'string', 'max:255'],
            'only_one' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            if (!$this->mikrotik->getProfileById($router, $profile)) {
                return response()->json(['success' => false, 'message' => 'PPP Profile tidak ditemukan.'], 404);
            }

            $this->mikrotik->updateProfile(
                $router,
                $profile,
                $data['name'],
                $data['local_address'] ?? null,
                $data['remote_address'] ?? null,
                $data['rate_limit'] ?? null,
                $data['only_one'] ?? null
            );

            return response()->json(['success' => true, 'message' => 'PPP Profile berhasil diupdate.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteProfile(Router $router, string $profile)
    {
        try {
            if (!$this->mikrotik->getProfileById($router, $profile)) {
                return response()->json(['success' => false, 'message' => 'PPP Profile tidak ditemukan.'], 404);
            }

            $this->mikrotik->deleteProfile($router, $profile);

            return response()->json(['success' => true, 'message' => 'PPP Profile berhasil dihapus.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function setSecretState(Router $router, string $secret, bool $enabled)
    {
        try {
            if (!$this->mikrotik->getSecretById($router, $secret)) {
                return response()->json(['success' => false, 'message' => 'PPP Secret tidak ditemukan.'], 404);
            }

            if ($enabled) {
                $this->mikrotik->enableSecretById($router, $secret);
            } else {
                $this->mikrotik->disableSecretById($router, $secret);
            }

            return response()->json([
                'success' => true,
                'message' => $enabled
                    ? 'PPP Secret berhasil diaktifkan.'
                    : 'PPP Secret berhasil dinonaktifkan.',
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
