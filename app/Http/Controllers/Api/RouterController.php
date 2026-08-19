<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RouterController as WebRouterController;
use App\Models\Router;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Router::all()]);
    }

    public function store(Request $request)
    {
        return $this->normalize(app(WebRouterController::class)->store($request));
    }

    public function show(Router $router)
    {
        return response()->json(['success' => true, 'data' => $router]);
    }

    public function update(Request $request, Router $router)
    {
        return $this->normalize(app(WebRouterController::class)->update($request, $router));
    }

    public function destroy(Router $router)
    {
        return $this->normalize(app(WebRouterController::class)->destroy($router));
    }

    public function test($router)
    {
        return $this->normalize(app(WebRouterController::class)->test($router));
    }

    public function pppSecret($router)
    {
        return $this->normalize(app(WebRouterController::class)->pppSecret($router));
    }

    public function pppProfile($router)
    {
        return $this->normalize(app(WebRouterController::class)->pppProfile($router));
    }

    public function createSecret($router)
    {
        return $this->normalize(app(WebRouterController::class)->createSecret($router));
    }

    public function storeSecret(Request $request, $router)
    {
        return $this->normalize(app(WebRouterController::class)->storeSecret($request, $router));
    }

    public function editSecret($router, $username)
    {
        return $this->normalize(app(WebRouterController::class)->editSecret($router, $username));
    }

    public function updateSecret(Request $request, $router, $secret)
    {
        return $this->normalize(app(WebRouterController::class)->updateSecret($request, $router, $secret));
    }

    public function deleteSecret($router, $secret)
    {
        return $this->normalize(app(WebRouterController::class)->deleteSecret($router, $secret));
    }

    public function enableSecret($router, $secret)
    {
        return $this->normalize(app(WebRouterController::class)->enableSecret($router, $secret));
    }

    public function disableSecret($router, $secret)
    {
        return $this->normalize(app(WebRouterController::class)->disableSecret($router, $secret));
    }

    public function createProfile($router)
    {
        return $this->normalize(app(WebRouterController::class)->createProfile($router));
    }

    public function storeProfile(Request $request, $router)
    {
        return $this->normalize(app(WebRouterController::class)->storeProfile($request, $router));
    }

    public function editProfile($router, $profile)
    {
        return $this->normalize(app(WebRouterController::class)->editProfile($router, $profile));
    }

    public function updateProfile(Request $request, $router, $profile)
    {
        return $this->normalize(app(WebRouterController::class)->updateProfile($request, $router, $profile));
    }

    public function deleteProfile($router, $profile)
    {
        return $this->normalize(app(WebRouterController::class)->deleteProfile($router, $profile));
    }

    private function normalize($response)
    {
        if ($response instanceof \Illuminate\Http\JsonResponse) return $response;
        return response()->json(['success' => true, 'message' => 'Operasi router berhasil.']);
    }
}
