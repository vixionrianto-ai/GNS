<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PelangganService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PelangganResource;
use App\Http\Resources\TagihanResource;
use App\Http\Resources\PembayaranResource;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Requests\PelangganRequest;


class PelangganController extends Controller
{
    use ApiResponse;
    public function index(
        Request $request,
        PelangganService $service
    ): JsonResponse {

        $data = $service->getList([
            'search' => $request->search,
            'status' => $request->status,
            'per_page' => $request->integer('per_page', 15),
        ]);

        return $this->success(
            PelangganResource::collection($data->items()),
            'Data pelanggan berhasil diambil.',
            200,
            [
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                    'from'         => $data->firstItem(),
                    'to'           => $data->lastItem(),
                ],
            ]
        );
    }
    public function show(
        int $id,
        PelangganService $service
    ): JsonResponse
    {
        return $this->success(
            new PelangganResource(
                $service->getDetail($id)
            ),
            'Detail pelanggan berhasil diambil.'
        );
    }
    public function tagihan(
        int $id,
        PelangganService $service
    ): JsonResponse
    {
        return $this->success(
            TagihanResource::collection(
                $service->getTagihan($id)
            ),
            'Data tagihan berhasil diambil.'
        );
    }
    public function pembayaran(
        int $id,
        PelangganService $service
    ): JsonResponse
    {
        return $this->success(
            PembayaranResource::collection(
                $service->getPembayaran($id)
            ),
            'Data pembayaran berhasil diambil.'
        );
    }
    public function store(
        PelangganRequest $request,
        PelangganService $service
    )
    {
        $pelanggan = $service->create(
            $request->validated()
        );

        return $this->success(
            new PelangganResource($pelanggan),
            'Pelanggan berhasil ditambahkan.'
        );
    }
    public function update(
        PelangganRequest $request,
        int $id,
        PelangganService $service
    )
    {
        $pelanggan = $service->update(
            $id,
            $request->validated()
        );

        return $this->success(
            new PelangganResource($pelanggan),
            'Pelanggan berhasil diperbarui.'
        );
    }
    public function destroy(
        int $id,
        PelangganService $service
    )
    {
        $service->delete($id);

        return $this->success(
            null,
            'Pelanggan berhasil dihapus.'
        );
    }
}