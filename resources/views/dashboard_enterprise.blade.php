@extends('adminlte::page')

@section('title', 'Dashboard Enterprise')

@section('css')
<link rel="stylesheet" href="{{ asset('css/dashboard-enterprise.css') }}">
@stop

@section('content_header')

<div class="dashboard-card mb-4">

    <div class="card-body py-3">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <div class="d-flex align-items-center">

                    <div class="dashboard-logo mr-3">

                        <i class="fas fa-network-wired"></i>

                    </div>

                    <div>

                        <h2 class="dashboard-title mb-1">Dashboard GNS Enterprise</h2>

                        <div class="dashboard-subtitle">
                            Billing Management System • Monitoring MikroTik • Dashboard Bisnis ISP
                        </div>

                        <div class="dashboard-badge">
                            <span class="badge badge-primary">Enterprise v4</span>
                            <span class="badge badge-success">ONLINE</span>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-4 text-lg-right mt-4 mt-lg-0">
                <div class="dashboard-user-name">{{ Auth::user()->name }}</div>
                <div class="dashboard-subtitle">
                    {{ method_exists(Auth::user(), 'getRoleNames') ? (Auth::user()->getRoleNames()->join(', ') ?: 'Belum ada role') : 'Pengguna' }}
                </div>
                <div class="mt-3">{{ now()->translatedFormat('l, d F Y') }}</div>
                <div id="clock" class="font-weight-bold"></div>
            </div>

        </div>

    </div>

</div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $totalPelanggan }}</h3><p>Total Pelanggan</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $pelangganAktif }}</h3><p>Pelanggan Aktif</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $tagihanBelumBayar }}</h3><p>Belum Bayar</p></div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>Rp {{ number_format($pendapatanBulanIni,0,',','.') }}</h3><p>Pendapatan Bulan Ini</p></div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-card mb-4">
                <div class="card-header"><h3 class="card-title mb-0">Status Jaringan</h3></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6"><div class="h3 text-primary mb-1">{{ $totalRouter }}</div><div class="text-muted">Total Router</div></div>
                        <div class="col-6"><div class="h3 text-success mb-1">{{ $routerAktif }}</div><div class="text-muted">Router Aktif</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card mb-4">
                <div class="card-header"><h3 class="card-title mb-0">Keuangan</h3></div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6"><div class="h3 text-success mb-1">Rp {{ number_format($pendapatanHariIni,0,',','.') }}</div><div class="text-muted">Pendapatan Hari Ini</div></div>
                        <div class="col-6"><div class="h3 text-primary mb-1">{{ $tagihanLunas }}</div><div class="text-muted">Tagihan Lunas</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="dashboard-card mb-4">
                <div class="card-header"><h3 class="card-title mb-0">Pembayaran Terakhir</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Total</th></tr></thead>
                            <tbody>
                            @forelse($pembayaranTerakhir as $pembayaran)
                                <tr>
                                    <td>{{ $pembayaran->invoice_no }}</td>
                                    <td>{{ optional(optional($pembayaran->tagihan)->pelanggan)->nama ?? '-' }}</td>
                                    <td>Rp {{ number_format($pembayaran->total_bayar ?? 0,0,',','.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card mb-4">
                <div class="card-header"><h3 class="card-title mb-0">Tagihan Jatuh Tempo</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Jatuh Tempo</th></tr></thead>
                            <tbody>
                            @forelse($tagihanJatuhTempo as $tagihan)
                                <tr>
                                    <td>{{ $tagihan->invoice_no }}</td>
                                    <td>{{ optional($tagihan->pelanggan)->nama ?? '-' }}</td>
                                    <td>{{ optional($tagihan->tanggal_jatuh_tempo)->format('d/m/Y') ?? $tagihan->tanggal_jatuh_tempo }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada tagihan jatuh tempo.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
(function(){
    const el = document.getElementById('clock');
    if (!el) return;
    function tick(){ el.textContent = new Date().toLocaleTimeString('id-ID'); }
    tick();
    setInterval(tick, 1000);
})();
</script>
@stop
