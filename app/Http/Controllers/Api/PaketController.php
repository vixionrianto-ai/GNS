<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\PaketResource;
use App\Services\PaketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\PaketRequest;

class PaketController extends Controller
{
    use ApiResponse;

    public function index(
        Request $request,
        PaketService $service
    ): JsonResponse {

        $pakets = $service->getList(
            $request->integer('per_page', 100)
        );

        return $this->success(

            PaketResource::collection(
                $pakets->items()
            ),

            'Data paket berhasil diambil.',

            200,

            [
                'pagination' => [

                    'current_page' => $pakets->currentPage(),

                    'last_page' => $pakets->lastPage(),

                    'per_page' => $pakets->perPage(),

                    'total' => $pakets->total(),

                    'from' => $pakets->firstItem(),

                    'to' => $pakets->lastItem(),

                ]

            ]

        );
    }

    public function show(
        int $id,
        PaketService $service
    ): JsonResponse {

        return $this->success(

            new PaketResource(

                $service->getDetail($id)

            ),

            'Detail paket berhasil diambil.'

        );
    }

    public function store(
        PaketRequest $request,
        PaketService $service
    ): JsonResponse {

        $paket = $service->create(
            $request->validated()
        );

        return $this->success(

            new PaketResource($paket),

            'Paket berhasil ditambahkan.',

            201

        );

    }

    public function update(
        PaketRequest $request,
        int $id,
        PaketService $service
    ): JsonResponse {

        $paket = $service->update(

            $id,

            $request->validated()

        );

        return $this->success(

            new PaketResource($paket),

            'Paket berhasil diperbarui.'

        );

    }

    public function destroy(
        int $id,
        PaketService $service
    ): JsonResponse {

        $service->delete($id);

        return $this->success(

            null,

            'Paket berhasil dihapus.'

        );

    }
}