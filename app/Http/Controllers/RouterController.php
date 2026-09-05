<?php

namespace App\Http\Controllers;

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
        $routers = Router::all();
        return view('router.index', compact('routers'));
    }

    public function create()
    {
        return view('router.create');
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
        Router::create($data);
        return redirect()->route('router.index')->with('success', 'Router berhasil ditambahkan.');
    }

    public function show(Router $router)
    {
        return redirect()->route('router.edit', $router);
    }

    public function edit(Router $router)
    {
        return view('router.edit', compact('router'));
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
        return redirect()->route('router.index')->with('success', 'Router berhasil diperbarui.');
    }

    public function test(Router $router)
    {
        $result = $this->mikrotik->testConnectionDetail($router);
        return redirect()->route('router.index')->with(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? 'Berhasil terhubung : ' . ($result['board'] ?? 'MikroTik') . ' | RouterOS ' . ($result['version'] ?? '-')
                : $result['message']
        );
    }

    public function pppSecret(Router $router)
    {
        try {
            $secrets = $this->mikrotik->getSecrets($router);
            return view('router.ppp-secret', compact('router', 'secrets'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    public function pppProfile(Router $router)
    {
        try {
            $profiles = $this->mikrotik->getProfiles($router);
            return view('router.ppp-profile', compact('router', 'profiles'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    public function createSecret(Router $router)
    {
        try {
            $profiles = $this->mikrotik->getProfileNames($router);
            return view('router.create-secret', compact('router', 'profiles'));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('router.pppsecret', $router)->with('error', $e->getMessage());
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
            $this->mikrotik->createSecret($router, $data['username'], $data['password'], $data['profile'], $data['service']);
            return redirect()->route('router.pppsecret', $router)->with('success', 'PPP Secret berhasil ditambahkan.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function editSecret(Router $router, string $username)
    {
        try {
            $secret = $this->mikrotik->getSecretByName($router, $username);
            if (!$secret) {
                return redirect()->route('router.pppsecret', $router)->with('error', 'PPP Secret tidak ditemukan.');
            }
            $profiles = $this->mikrotik->getProfileNames($router);
            return view('router.edit-secret', compact('router', 'secret', 'profiles'));
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('router.pppsecret', $router)->with('error', $e->getMessage());
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
            $existing = $this->mikrotik->getSecretByName($router, $secret);
            $secretId = $existing['.id'] ?? null;
            if (!$secretId) {
                return back()->with('error', 'PPP Secret tidak ditemukan.');
            }
            $this->mikrotik->updateSecretById(
                $router,
                (string) $secretId,
                $data['username'],
                $data['password'] ?? null,
                $data['profile'],
                $data['service'],
                $data['disabled']
            );
            return redirect()->route('router.pppsecret', $router)->with('success', 'PPP Secret berhasil diupdate.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteSecret(Router $router, string $secret)
    {
        try {
            if (!$this->mikrotik->getSecretById($router, $secret)) {
                return back()->with('error', 'PPP Secret tidak ditemukan.');
            }
            $this->mikrotik->disconnectActiveSessionBySecretId($router, $secret);
            $this->mikrotik->deleteSecretById($router, $secret);
            return redirect()->route('router.pppsecret', $router)->with('success', 'PPP Secret berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('router.pppsecret', $router)->with('error', $e->getMessage());
        }
    }

    public function enableSecret(Router $router, string $secret)
    {
        try {
            $this->mikrotik->enableSecretById($router, $secret);
            return redirect()->route('router.pppsecret', $router)->with('success', 'PPP Secret berhasil diaktifkan.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    public function disableSecret(Router $router, string $secret)
    {
        try {
            $this->mikrotik->disableSecretById($router, $secret);
            $this->mikrotik->disconnectActiveSessionBySecretId($router, $secret);
            return redirect()->route('router.pppsecret', $router)->with('success', 'PPP Secret berhasil dinonaktifkan.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    public function createProfile(Router $router)
    {
        return view('router.ppp-profile-create', compact('router'));
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
            $this->mikrotik->createProfile($router, $data['name'], $data['local_address'] ?? null, $data['remote_address'] ?? null, $data['rate_limit'] ?? null, $data['only_one'] ?? null);
            return redirect()->route('router.pppprofile', $router)->with('success', 'PPP Profile berhasil ditambahkan.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function editProfile(Router $router, string $profile)
    {
        try {
            $data = $this->mikrotik->getProfileById($router, $profile);
            if (!$data) {
                return back()->with('error', 'PPP Profile tidak ditemukan.');
            }
            return view('router.ppp-profile-edit', compact('router', 'data'));
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
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
            $this->mikrotik->updateProfile($router, $profile, $data['name'], $data['local_address'] ?? null, $data['remote_address'] ?? null, $data['rate_limit'] ?? null, $data['only_one'] ?? null);
            return redirect()->route('router.pppprofile', $router)->with('success', 'PPP Profile berhasil diupdate.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function deleteProfile(Router $router, string $profile)
    {
        try {
            $this->mikrotik->deleteProfile($router, $profile);
            return redirect()->route('router.pppprofile', $router)->with('success', 'PPP Profile berhasil dihapus.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Router $router)
    {
        if ($router->pelanggans()->exists()) {
            return redirect()->route('router.index')
                ->with('error', 'Router tidak dapat dihapus karena masih digunakan pelanggan.');
        }

        $router->delete();
        return redirect()->route('router.index')->with('success', 'Router berhasil dihapus.');
    }
}
