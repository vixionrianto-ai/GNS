<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\TagihanResource;
use App\Services\TagihanService;
use App\Services\WhatsAppTagihanService;
use Illuminate\Http\JsonResponse;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Http\Requests\ApiGenerateTagihanRequest;
use Carbon\Carbon;

class TagihanController extends Controller
{
    use ApiResponse;

    // Ubah method index API ini agar tidak pakai pagination yang bikin Android zonk
    public function index(): JsonResponse
    {
        $tagihans = Tagihan::with(['pelanggan', 'pelanggan.paket'])
            ->latest('id')
            ->get();

        return $this->success(
            TagihanResource::collection($tagihans),
            'Data semua tagihan berhasil diambil.'
        );
    }

    public function getTagihanJatuhTempoApi(): JsonResponse
    {
        $tagihans = Tagihan::with('pelanggan')
            ->where('status', '!=', Tagihan::STATUS_LUNAS)
            ->get();

        return $this->success(
            TagihanResource::collection($tagihans),
            'Data tagihan jatuh tempo berhasil diambil.'
        );
    }

    public function show(
        int $id,
        TagihanService $service
    ): JsonResponse {

        return $this->success(
            new TagihanResource(
                $service->getDetail($id)
            ),
            'Detail tagihan berhasil diambil.'
        );
    }

    public function whatsapp(
        Tagihan $tagihan,
        WhatsAppTagihanService $whatsapp
    ): JsonResponse {
        $data = $whatsapp->forTagihan($tagihan);

        if (empty($data['url'])) {
            return $this->error(
                'Nomor WhatsApp pelanggan tidak tersedia.',
                422
            );
        }

        return $this->success(
            $data,
            'Aksi WhatsApp tagihan berhasil disiapkan.'
        );
    }

    public function generate(
        Pelanggan $pelanggan,
        ApiGenerateTagihanRequest $request,
        TagihanService $service
    ): JsonResponse {

        $tanggal = $request->filled('tanggal')
            ? Carbon::parse($request->tanggal)
            : Carbon::today();

        $tagihan = $service->generateUntukPeriode(
            $pelanggan,
            $tanggal
        );

        return $this->created(
            new TagihanResource($tagihan),
            'Tagihan berhasil dibuat.'
        );
    }

    public function generateSemua(
        ApiGenerateTagihanRequest $request,
        TagihanService $service
    ): JsonResponse {

        $periode = $request->filled('tanggal')
            ? Carbon::parse($request->tanggal)
            : null;

        $hasil = $service->generateSemua($periode);

        return $this->success(
            $hasil,
            'Generate seluruh tagihan berhasil.'
        );
    }

    public function generatePeriode(
        ApiGenerateTagihanRequest $request,
        TagihanService $service
    ): JsonResponse {

        $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $hasil = $service->generatePeriode(
            Carbon::parse($request->tanggal)
        );

        return $this->success(
            $hasil,
            'Generate tagihan berdasarkan periode berhasil.'
        );
    }

    public function regenerate(
        Tagihan $tagihan,
        TagihanService $service
    ): JsonResponse {

        $tagihanBaru = $service->regenerate($tagihan);

        return $this->success(
            new TagihanResource($tagihanBaru),
            'Invoice berhasil diregenerate.'
        );
    }

    public function maintenance(
        TagihanService $service
    ): JsonResponse {

        $service->maintenanceHarian();

        return $this->success(
            null,
            'Maintenance tagihan berhasil dijalankan.'
        );
    }
}