<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LaporanService;

class LaporanController extends Controller
{
    /**
     * Service laporan.
     */
    protected LaporanService $laporanService;

    /**
     * Constructor.
     */
    public function __construct(
        LaporanService $laporanService
    )
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Dashboard laporan.
     */
    public function index(Request $request)
    {
        return view(
            'laporan.index',
            $this->laporanService->dashboard($request)
        );
    }
}