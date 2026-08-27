<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataResetController extends Controller
{
    public function reset(Request $request)
    {
        $request->validate([
            'confirmation' => ['required', 'in:RESET DATA'],
        ]);

        DB::transaction(function () {
            // Hanya database GNS. Tidak memanggil MikroTikService.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ([
                'alokasi_pembayarans',
                'saldo_usages',
                'saldo_pelanggans',
                'pembayarans',
                'tagihans',
                'whats_app_logs',
                'pelanggans',
            ] as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return back()->with('success', 'Data pelanggan dan transaksi berhasil dikosongkan. Data MikroTik tidak disentuh.');
    }
}
