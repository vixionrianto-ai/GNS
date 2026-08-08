<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OperatorDashboardResource;
use App\Services\OperatorService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\OperatorResource;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    use ApiResponse;
    public function home(
        OperatorService $service
    ): JsonResponse
    {
        return $this->success(
            new OperatorDashboardResource(
                $service->dashboard()
            ),
            'Dashboard operator berhasil diambil.'
        );
    }
    public function profile(
        OperatorService $service
    ): JsonResponse {

        return $this->success(
            new OperatorResource(
                $service->profile()
            ),
            'Profil operator berhasil diambil.'
        );
    }
    public function updateProfile(
        Request $request,
        OperatorService $service
    ): JsonResponse {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        return $this->success(
            new OperatorResource(
                $service->updateProfile(
                    $request->only([
                        'name',
                        'email',
                    ])
                )
            ),
            'Profil berhasil diperbarui.'
        );
    }
    public function changePassword(
        Request $request,
        OperatorService $service
    ): JsonResponse {

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $service->changePassword(
            $request->all()
        );

        return $this->success(
            null,
            'Password berhasil diubah.'
        );
    }
}