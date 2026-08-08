<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Requests\RouterRequest;
use App\Http\Resources\RouterResource;
use App\Services\RouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Router;
use App\Services\MikroTikService;

class RouterController extends Controller
{
    use ApiResponse;

    public function index(
        Request $request,
        RouterService $service
    ): JsonResponse {

        $routers = $service->getList(
            $request->integer('per_page', 15)
        );

        return $this->success(
            RouterResource::collection(
                $routers->items()
            ),
            'Data router berhasil diambil.',
            200,
            [
                'pagination' => [
                    'current_page' => $routers->currentPage(),
                    'last_page'    => $routers->lastPage(),
                    'per_page'     => $routers->perPage(),
                    'total'        => $routers->total(),
                    'from'         => $routers->firstItem(),
                    'to'           => $routers->lastItem(),
                ],
            ]
        );
    }

    public function show(
        int $id,
        RouterService $service
    ): JsonResponse {

        return $this->success(
            new RouterResource(
                $service->getDetail($id)
            ),
            'Detail router berhasil diambil.'
        );
    }

    public function store(
        RouterRequest $request,
        RouterService $service
    ): JsonResponse {

        $router = $service->create(
            $request->validated()
        );

        return $this->created(
            new RouterResource($router),
            'Router berhasil ditambahkan.'
        );
    }

    public function update(
        RouterRequest $request,
        int $id,
        RouterService $service
    ): JsonResponse {

        $router = $service->update(
            $id,
            $request->validated()
        );

        return $this->success(
            new RouterResource($router),
            'Router berhasil diperbarui.'
        );
    }

    public function destroy(
        int $id,
        RouterService $service
    ): JsonResponse {

        $service->delete($id);

        return $this->deleted(
            'Router berhasil dihapus.'
        );
    }
    public function testConnection(
        int $id,
        MikroTikService $mikrotik
    ): JsonResponse {

        $router = Router::findOrFail($id);

        $connected = $mikrotik->testConnection($router);

        if (! $connected) {

            return $this->error(
                'Gagal terhubung ke router.',
                400
            );

        }

        return $this->success(
            [
                'connected' => true,
            ],
            'Router berhasil terhubung.'
        );
    }
    public function info(
        int $id,
        MikroTikService $mikrotik
    ): JsonResponse {

        $router = Router::findOrFail($id);

        return $this->success([

            'identity' => $mikrotik->getIdentity($router),

            'version' => $mikrotik->getRouterVersion($router),

            'board' => $mikrotik->getBoardName($router),

            'uptime' => $mikrotik->getUptime($router),

            'secret_count' => $mikrotik->getSecretCount($router),

            'active_count' => $mikrotik->getActivePppCount($router),

        ], 'Informasi router berhasil diambil.');
    }
    public function profiles(
    int $id,
    MikroTikService $mikrotik
    ): JsonResponse
    {
        $router = Router::findOrFail($id);

        return $this->success(
            $mikrotik->getProfileNames($router),
            'PPP Profile berhasil diambil.'
        );
    }
    public function secrets(
        int $id,
        MikroTikService $mikrotik
    ): JsonResponse
    {
        $router = Router::findOrFail($id);

        return $this->success(
            $mikrotik->getSecrets($router),
            'PPP Secret berhasil diambil.'
        );
    }
    public function active(
        int $id,
        MikroTikService $mikrotik
    ): JsonResponse
    {
        $router = Router::findOrFail($id);

        return $this->success(
            $mikrotik->getActiveSessions($router),
            'PPP Active berhasil diambil.'
        );
    }
    public function createSecret(
        Request $request,
        int $id,
        MikroTikService $mikrotik
    ): JsonResponse {

        $request->validate([

            'username' => 'required|string',

            'password' => 'required|string',

            'profile' => 'required|string',

            'service' => 'nullable|string',

        ]);

        $router = Router::findOrFail($id);

        $secretId = $mikrotik->createSecret(

            $router,

            $request->username,

            $request->password,

            $request->profile,

            $request->service ?? 'pppoe'

        );

        return $this->created(

            [

                'secret_id' => $secretId,

            ],

            'PPP Secret berhasil dibuat.'

        );

    }
    public function updateSecret(
        Request $request,
        int $id,
        string $secret,
        MikroTikService $mikrotik
    ): JsonResponse {

        $request->validate([

            'username' => 'required|string',

            'password' => 'required|string',

            'profile' => 'required|string',

            'service' => 'nullable|string',

        ]);

        $router = Router::findOrFail($id);

        $mikrotik->updateSecretById(

            $router,

            $secret,

            $request->username,

            $request->password,

            $request->profile,

            $request->service ?? 'pppoe'

        );

        return $this->success(

            [],

            'PPP Secret berhasil diperbarui.'

        );

    }
    public function deleteSecret(
        int $id,
        string $secret,
        MikroTikService $mikrotik
    ): JsonResponse {

        $router = Router::findOrFail($id);

        $mikrotik->deleteSecretById(

            $router,

            $secret

        );

        return $this->deleted(

            'PPP Secret berhasil dihapus.'

        );

    }
    public function enableSecret(
        int $id,
        string $secret,
        MikroTikService $mikrotik
    ): JsonResponse {

        $router = Router::findOrFail($id);

        $mikrotik->enableSecretById(

            $router,

            $secret

        );

        return $this->success(

            [],

            'PPP Secret berhasil diaktifkan.'

        );

    }
    public function disableSecret(
        int $id,
        string $secret,
        MikroTikService $mikrotik
    ): JsonResponse {

        $router = Router::findOrFail($id);

        $mikrotik->disableSecretById(

            $router,

            $secret

        );

        return $this->success(

            [],

            'PPP Secret berhasil dinonaktifkan.'

        );

    }
    public function disconnectSecret(
        int $id,
        string $secret,
        MikroTikService $mikrotik
    ): JsonResponse {

        $router = Router::findOrFail($id);

        $mikrotik->disconnectActiveSessionBySecretId(

            $router,

            $secret

        );

        return $this->success(

            [],

            'PPP Session berhasil diputus.'

        );

    }
}