<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchResource;
use App\Http\Resources\TagihanSearchResource;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $service)
    {
        $keyword = trim($request->q ?? '');

        if ($keyword === '') {
            return response()->json([
                'success' => true,
                'data' => [
                    'pelanggan' => [],
                    'tagihan' => [],
                ],
            ]);
        }

        $hasil = $service->searchAll($keyword);

        return response()->json([
            'success' => true,
            'data' => [
                'pelanggan' => SearchResource::collection(
                    $hasil['pelanggan']
                ),
                'tagihan' => TagihanSearchResource::collection(
                    $hasil['tagihan']
                ),
            ],
        ]);
    }
}