<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Router;
use App\Services\MikroTikService;
use App\Services\PppMonitoringService;
use Illuminate\Http\Request;

class PppMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $routerId = $request->integer('router_id');
        $routers = Router::query()->orderBy('nama_router')->get();

        $pelanggans = Pelanggan::query()->with(['router', 'paket'])
            ->whereNotNull('username_pppoe')->where('username_pppoe', '!=', '')
            ->when($routerId, fn ($q) => $q->where('router_id', $routerId))
            ->when($request->filled('status'), fn ($q) => $q->where('ppp_status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->string('search')->toString();
                $q->where(fn ($sub) => $sub->where('nama','like',"%{$s}%")
                    ->orWhere('kode_pelanggan','like',"%{$s}%")
                    ->orWhere('username_pppoe','like',"%{$s}%"));
            })->latest()->paginate(25)->withQueryString();

        $base = Pelanggan::query()->whereNotNull('username_pppoe')->where('username_pppoe','!=')
            ->when($routerId, fn ($q) => $q->where('router_id',$routerId));
        $summary = [
            'online'=>(clone $base)->where('ppp_status','online')->count(),
            'offline'=>(clone $base)->where('ppp_status','offline')->count(),
            'disabled'=>(clone $base)->where('ppp_status','disabled')->count(),
            'router_offline'=>(clone $base)->where('ppp_status','router_offline')->count(),
            'unknown'=>(clone $base)->where('ppp_status','unknown')->count(),
        ];

        return view('router.ppp-monitoring', compact('pelanggans','routers','routerId','summary'));
    }

    public function sync(PppMonitoringService $service)
    {
        $results = $service->syncAll();
        $online = $offline = $disabled = $routerOffline = 0;
        foreach ($results as $result) {
            $online += $result['online']; $offline += $result['offline']; $disabled += $result['disabled'];
            if (!$result['success']) $routerOffline++;
        }

        return redirect()->route('mikrotik.ppp.monitor')->with(
            'success',
            "Monitoring diperbarui. Online: {$online}, Offline: {$offline}, Disabled: {$disabled}, Router offline: {$routerOffline}."
        );
    }

    public function active(Router $router, MikroTikService $mikrotik)
    {
        try {
            $sessions = $mikrotik->getActiveSessions($router);
            return view('router.ppp-active', compact('router','sessions'));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function disconnect(Router $router, string $session, MikroTikService $mikrotik)
    {
        try {
            $ok = $mikrotik->disconnectActiveSession($router, $session);
            return back()->with($ok ? 'success' : 'error', $ok ? 'PPP Session berhasil diputus.' : 'PPP Session tidak ditemukan.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
