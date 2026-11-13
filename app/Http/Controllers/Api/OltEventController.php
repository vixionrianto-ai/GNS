<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOltDisconnect;
use App\Jobs\ProcessOltRecovery;
use App\Models\Pelanggan;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class OltEventController extends Controller
{
    public function __invoke(Request $request)
    {
        $expectedToken = (string) config('services.olt_event.token', '');
        $providedToken = (string) $request->header('X-OLT-Event-Token', '');

        if ($expectedToken === '' || $providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $validated = $request->validate([
            'router' => ['required', 'string', 'max:80'],
            'username' => ['required', 'string', 'max:191'],
            'event' => ['required', 'in:down,up'],
        ]);

        $router = Router::query()
            ->whereRaw('UPPER(nama_router) = ?', [mb_strtoupper(trim($validated['router']))])
            ->first();

        if (!$router) {
            return response()->json(['ok' => false, 'message' => 'Router tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        // Alur notifikasi event ini sengaja dibatasi hanya untuk KUWU.
        if (strtoupper(trim((string) $router->nama_router)) !== 'KUWU') {
            return response()->json(['ok' => false, 'message' => 'Event OLT hanya diizinkan untuk router KUWU'], Response::HTTP_FORBIDDEN);
        }

        $customer = Pelanggan::query()
            ->with(['router', 'paket'])
            ->where('router_id', $router->id)
            ->where('username_pppoe', $validated['username'])
            ->where('status', Pelanggan::AKTIF)
            ->first();

        if (!$customer || !$customer->router) {
            return response()->json(['ok' => false, 'message' => 'Pelanggan aktif tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        // Endpoint event ini khusus pelanggan dengan PPP Profile C20.
        if (strtoupper(trim((string) ($customer->paket?->profile_mikrotik ?? ''))) !== 'C20') {
            return response()->json(['ok' => false, 'message' => 'Event hanya diizinkan untuk PPP Profile C20'], Response::HTTP_FORBIDDEN);
        }

        $pendingKey = 'olt:kuwu:pending-down:' . $customer->id;

        if ($validated['event'] === 'down') {
            // Setiap DOWN memiliki ID sendiri. DOWN lama otomatis gugur jika ada UP/DOWN baru.
            $eventId = (string) Str::uuid();
            Cache::put($pendingKey, $eventId, now()->addMinutes(15));

            ProcessOltDisconnect::dispatch((int) $customer->id, $eventId)
                ->delay(now()->addSeconds(30));

            Log::info('Event PPP KUWU down masuk antrean.', [
                'pelanggan_id' => $customer->id,
                'username' => $customer->username_pppoe,
                'event_id' => $eventId,
                'delay_seconds' => 30,
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Event disconnect masuk antrean.',
                'customer' => $customer->nama,
                'event' => 'down',
                'delay_seconds' => 30,
            ], Response::HTTP_ACCEPTED);
        }

        // UP langsung membatalkan DOWN yang masih menunggu. Pemulihan diproses oleh queue,
        // sehingga MikroTik tidak menunggu koneksi atau pembacaan OLT.
        Cache::forget($pendingKey);
        ProcessOltRecovery::dispatch((int) $customer->id);

        Log::info('Event PPP KUWU up masuk antrean pemulihan.', [
            'pelanggan_id' => $customer->id,
            'username' => $customer->username_pppoe,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Event recovery masuk antrean.',
            'customer' => $customer->nama,
            'event' => 'up',
        ], Response::HTTP_ACCEPTED);
    }
}
