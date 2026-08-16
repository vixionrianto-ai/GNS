<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\BillingConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    protected BillingConfigurationService $billingConfiguration;

    public function __construct(BillingConfigurationService $billingConfiguration)
    {
        $this->billingConfiguration = $billingConfiguration;
    }

    /**
     * Halaman Pengaturan.
     */
    public function index()
    {
        $settings = Setting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    /**
     * Simpan pengaturan dan sinkronkan konfigurasi billing sekali saja.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => ['array'],
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('settings', []) as $id => $value) {
                Setting::where('id', $id)->update([
                    'value' => $value,
                ]);
            }

            $this->billingConfiguration->sync();
        });

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
