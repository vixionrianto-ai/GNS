<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppLog;
use Illuminate\Http\Request;

class WhatsAppLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    /**
     * Statistik Dashboard WhatsApp.
     */
    private function statistik(): array
    {
        return [

            'totalLog' => WhatsAppLog::count(),

            'totalSuccess' => WhatsAppLog::where(
                'status',
                'success'
            )->count(),

            'totalFailed' => WhatsAppLog::where(
                'status',
                'failed'
            )->count(),

            'totalPending' => WhatsAppLog::where(
                'status',
                'pending'
            )->count(),

            'totalHariIni' => WhatsAppLog::whereDate(
                'sent_at',
                today()
            )->count(),

        ];
    }

    /**
     * Terapkan filter.
     */
    private function filter(
        $query,
        Request $request
    )
    {
        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Jenis
        |--------------------------------------------------------------------------
        */

        if ($request->filled('jenis')) {

            $query->where(
                'jenis',
                $request->jenis
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Provider
        |--------------------------------------------------------------------------
        */

        if ($request->filled('provider')) {

            $query->where(
                'provider',
                $request->provider
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tanggal')) {

            $query->whereDate(
                'sent_at',
                $request->tanggal
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'nomor',
                    'like',
                    "%{$search}%"
                )

                ->orWhereHas(
                    'pelanggan',
                    function ($pelanggan) use ($search) {

                        $pelanggan->where(
                            'nama',
                            'like',
                            "%{$search}%"
                        );

                    }
                )

                ->orWhereHas(
                    'tagihan',
                    function ($tagihan) use ($search) {

                        $tagihan->where(
                            'invoice_no',
                            'like',
                            "%{$search}%"
                        );

                    }
                );

            });

        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat WhatsApp
    |--------------------------------------------------------------------------
    */

    /**
     * Daftar Riwayat WhatsApp.
     */
    public function index(
        Request $request
    )
    {
        /*
        |--------------------------------------------------------------------------
        | Query
        |--------------------------------------------------------------------------
        */

        $query = WhatsAppLog::with([

            'pelanggan',

            'tagihan',

        ]);

        $query = $this->filter(
            $query,
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Data
        |--------------------------------------------------------------------------
        */

        $logs = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */

        $statistik = $this->statistik();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        return view(
            'whatsapp.index',
            array_merge(
                [
                    'logs' => $logs,
                ],
                $statistik
            )
        );
    }

    /**
     * Detail Riwayat WhatsApp.
     */
    public function show(
        WhatsAppLog $whatsapp
    )
    {
        $whatsapp->load([

            'pelanggan',

            'tagihan',

        ]);

        return view(
            'whatsapp.show',
            [
                'log' => $whatsapp,
            ]
        );
    }
}