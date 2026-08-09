@extends('adminlte::page')

@section('title', 'Dashboard Enterprise')

@section('css')
<link rel="stylesheet" href="{{ asset('css/dashboard-enterprise.css') }}">
@stop 

@section('content_header')

<div class="dashboard-card mb-4">

    <div class="card-body py-3">

        <div class="row align-items-center">

            {{-- LEFT --}}
            <div class="col-lg-8">

                <div class="d-flex align-items-center">

                    <div class="dashboard-logo mr-3">

                        <i class="fas fa-network-wired"></i>

                    </div>

                    <div>

                        <h2 class="dashboard-title mb-1">

                            Dashboard GNS Enterprise

                        </h2>

                        <div class="dashboard-subtitle">

                            Billing Management System • Monitoring MikroTik • Dashboard Bisnis ISP

                        </div>

                        <div class="dashboard-badge">

                            <span class="badge badge-primary">

                                Enterprise v4

                            </span>

                            <span class="badge badge-success">

                                ONLINE

                            </span>

                        </div>

                    </div>

                </div>

            </div>

            {{-- RIGHT --}}
            <div class="col-lg-4 text-lg-right mt-4 mt-lg-0">

                <div class="dashboard-user-name">

                    {{ Auth::user()->name }}

                </div>

                <div class="dashboard-subtitle">

                    Administrator

                </div>

                <div class="mt-3">

                    {{ now()->translatedFormat('l, d F Y') }}

                </div>

                <div class="dashboard-clock mt-1">

                    <span id="clock"></span>

                </div>

            </div>

        </div>

    </div>

</div>

@stop


@section('content')
{{-- ============================= --}}
{{-- KPI DASHBOARD --}}
{{-- ============================= --}}

<div class="row">

    {{-- Total Pelanggan --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            TOTAL PELANGGAN
                        </div>

                        <div class="kpi-value">
                            {{ number_format($totalPelanggan) }}
                        </div>

                        <div class="kpi-desc text-success">
                            {{ number_format($pelangganAktif) }} pelanggan aktif
                        </div>

                    </div>

                    <div class="kpi-icon icon-primary">
                        <i class="fas fa-users"></i>
                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Pendapatan --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            PENDAPATAN BULAN INI
                        </div>

                        <div class="kpi-money">
                            Rp {{ number_format($pendapatanBulanIni,0,',','.') }}
                        </div>

                        <div class="kpi-desc text-primary">
                            Hari ini: Rp {{ number_format($pendapatanHariIni,0,',','.') }}
                        </div>

                    </div>

                    <div class="kpi-icon icon-success">

                        <i class="fas fa-wallet"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Tagihan Belum Lunas --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            TAGIHAN BELUM LUNAS
                        </div>

                        <div class="kpi-value">
                            {{ number_format($tagihanBelumLunas) }}
                        </div>

                        <div class="kpi-desc text-danger">
                            Menunggu Pembayaran
                        </div>

                    </div>

                    <div class="kpi-icon icon-danger">

                        <i class="fas fa-file-invoice"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Collection Rate --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            COLLECTION RATE
                        </div>

                        <div class="kpi-value">
                            {{ number_format($collectionRate,1) }}%
                        </div>

                        <div class="kpi-desc text-success">

                            {{ number_format($tagihanLunas) }}
                            dari
                            {{ number_format($totalTagihan) }}
                            Tagihan

                        </div>

                    </div>

                    <div class="kpi-icon icon-info">

                        <i class="fas fa-chart-pie"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
{{-- ========================================================= --}}
{{-- KPI BISNIS --}}
{{-- ========================================================= --}}

<div class="row">

    {{-- Tagihan Lunas --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            TAGIHAN LUNAS
                        </div>

                        <div class="kpi-value">
                            {{ number_format($tagihanLunas) }}
                        </div>

                        <div class="kpi-desc text-success">
                            Sudah Dibayar
                        </div>

                    </div>

                    <div class="kpi-icon icon-success">

                        <i class="fas fa-check-circle"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Tagihan Sebagian --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            TAGIHAN SEBAGIAN
                        </div>

                        <div class="kpi-value">
                            {{ number_format($tagihanSebagian) }}
                        </div>

                        <div class="kpi-desc text-warning">
                            Pembayaran Bertahap
                        </div>

                    </div>

                    <div class="kpi-icon icon-warning">

                        <i class="fas fa-percentage"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Total Piutang --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            TOTAL PIUTANG
                        </div>

                        <div class="kpi-money">
                            Rp {{ number_format($totalPiutang,0,',','.') }}
                        </div>

                        <div class="kpi-desc text-danger">
                            Belum Tertagih
                        </div>

                    </div>

                    <div class="kpi-icon icon-danger">

                        <i class="fas fa-hand-holding-usd"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Saldo Pelanggan --}}
    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="kpi-top">

                    <div>

                        <div class="small-title">
                            SALDO PELANGGAN
                        </div>

                        <div class="kpi-money">
                            Rp {{ number_format($totalSaldoPelanggan,0,',','.') }}
                        </div>

                        <div class="kpi-desc text-success">
                            Deposit Pelanggan
                        </div>

                    </div>

                    <div class="kpi-icon icon-success">

                        <i class="fas fa-wallet"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
<div class="row">
{{-- Grafik --}}
<div class="col-lg-6 mb-4">

    <div class="card dashboard-card chart-card h-100">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>

                <h4 class="font-weight-bold mb-1">

                    <i class="fas fa-chart-line text-primary mr-2"></i>

                    Grafik Pendapatan

                </h4>

                <small class="text-muted">

                    Pendapatan 12 bulan terakhir

                </small>

            </div>

            <span class="badge badge-success">

                {{ now()->year }}

            </span>

        </div>

        <div class="card-body">

            <div class="chart-container">

                <canvas id="incomeChart"></canvas>

            </div>

        </div>

    </div>

</div>

{{-- Statistik Tagihan --}}
<div class="col-lg-2 mb-4">

    <div class="card dashboard-card info-card h-100">

        <div class="card-header">

            <h5>

                <i class="fas fa-chart-pie text-primary mr-2"></i>

                Statistik Tagihan

            </h5>

        </div>

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <span>

                    <i class="fas fa-circle text-success mr-1"></i>

                    Lunas

                </span>

                <strong>

                    {{ number_format($tagihanLunas) }}

                </strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>

                    <i class="fas fa-circle text-warning mr-1"></i>

                    Sebagian

                </span>

                <strong>

                    {{ number_format($tagihanSebagian) }}

                </strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>

                    <i class="fas fa-circle text-danger mr-1"></i>

                    Belum Bayar

                </span>

                <strong>

                    {{ number_format($tagihanBelumBayar) }}

                </strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>

                    <i class="fas fa-circle text-secondary mr-1"></i>

                    Jatuh Tempo

                </span>

                <strong>

                    {{ count($tagihanJatuhTempo) }}

                </strong>

            </div>

            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-center">

                <strong>Total</strong>

                <strong class="text-primary">

                    {{ number_format($totalTagihan) }}

                </strong>

            </div>

        </div>

    </div>

</div>
{{-- Status Sistem --}}
<div class="col-lg-2 mb-4">

    <div class="card dashboard-card info-card h-100">

        <div class="card-header">

            <h5>

                <i class="fas fa-server text-primary mr-2"></i>

                Status Sistem

            </h5>

        </div>

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <span>Versi GNS</span>

                <strong>Enterprise v4</strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>Laravel</span>

                <strong>{{ app()->version() }}</strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>PHP</span>

                <strong>{{ PHP_VERSION }}</strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>Status</span>

                <span class="badge badge-success">
                    ONLINE
                </span>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>Jam Server</span>

                <strong id="serverClock"></strong>

            </div>

            <div class="d-flex justify-content-between align-items-center">

                <span>Timezone</span>

                <strong>Asia/Jakarta</strong>

            </div>

        </div>

    </div>

</div>

{{-- Monitoring MikroTik --}}
<div class="col-lg-2 mb-4">

    <div class="card dashboard-card info-card h-100">

        <div class="card-header">

            <h5>

                <i class="fas fa-network-wired text-primary mr-2"></i>

                Monitoring

            </h5>

        </div>

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <span>Status</span>

                @if($mikrotikStatus)

                    <span class="badge badge-success">
                        ONLINE
                    </span>

                @else

                    <span class="badge badge-danger">
                        OFFLINE
                    </span>

                @endif

            </div>

            <div class="mt-3">

                <small class="font-weight-bold d-block mb-1">

                    CPU Usage

                </small>

                <div class="progress mb-1">

                    <div class="progress-bar bg-success"
                         style="width: {{ $routerCpu }}%">

                    </div>

                </div>

                <div class="text-right">

                    {{ $routerCpu }}%

                </div>

            </div>

            <div class="mt-3">

                <small class="font-weight-bold d-block mb-1">

                    Memory

                </small>

                <div class="progress mb-1">

                    <div class="progress-bar bg-info"
                         style="width: 52%">

                    </div>

                </div>

                <div class="text-right">

                    {{ $routerMemory }}

                </div>

            </div>

            <hr class="my-3">

            <div class="d-flex justify-content-between align-items-center">

                <span>Router</span>

                <strong>

                    {{ number_format($totalRouter) }}

                </strong>

            </div>

        </div>

    </div>

</div>
</div>
{{-- ========================================================= --}}
{{-- QUICK ACTION --}}
{{-- ========================================================= --}}

<div class="row">

    <div class="col-12 mb-4">

        <div class="card dashboard-card">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="font-weight-bold mb-1">

                        <i class="fas fa-bolt text-warning mr-2"></i>

                        Quick Action

                    </h4>

                    <small class="text-muted">

                        Akses cepat ke menu utama GNS Enterprise

                    </small>

                </div>

            </div>

            <div class="card-body">

                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6">

                    <div class="col mb-3">

                        <a href="{{ route('pelanggan.create') }}"
                           class="btn btn-light border quick-btn w-100">

                            <i class="fas fa-user-plus text-primary"></i>

                            <div class="mt-2">
                                Pelanggan
                            </div>

                        </a>

                    </div>

                    <div class="col mb-3">

                        <a href="{{ route('paket.create') }}"
                           class="btn btn-light border quick-btn w-100">

                            <i class="fas fa-wifi text-success"></i>

                            <div class="mt-2">
                                Paket
                            </div>

                        </a>

                    </div>

                    <div class="col mb-3">

                        <a href="{{ route('tagihan.index') }}"
                           class="btn btn-light border quick-btn w-100">

                            <i class="fas fa-file-invoice text-danger"></i>

                            <div class="mt-2">
                                Tagihan
                            </div>

                        </a>

                    </div>

                    <div class="col mb-3">

                        <a href="{{ route('pembayaran.index') }}"
                           class="btn btn-light border quick-btn w-100">

                            <i class="fas fa-money-check-alt text-warning"></i>

                            <div class="mt-2">
                                Pembayaran
                            </div>

                        </a>

                    </div>

                    {{-- PERBAIKAN: Diganti dari route('router.index') menjadi url('/router') --}}
                    <div class="col mb-3">

                        <a href="{{ url('/router') }}"
                           class="btn btn-light border quick-btn w-100">

                            <i class="fas fa-network-wired text-info"></i>

                            <div class="mt-2">
                                MikroTik
                            </div>

                        </a>

                    </div>

                    <div class="col mb-3">

                        <a href="{{ route('laporan.index') }}"
                           class="btn btn-light border quick-btn w-100">

                            <i class="fas fa-chart-bar text-success"></i>

                            <div class="mt-2">
                                Laporan
                            </div>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- ========================================================= --}}
{{-- PEMBAYARAN TERAKHIR + AUDIT TRAIL --}}
{{-- ========================================================= --}}

<div class="row">

    <div class="col-lg-7 mb-4">

    <div class="card dashboard-card h-100">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>

                <h4 class="font-weight-bold mb-1">

                    <i class="fas fa-money-check-alt text-success mr-2"></i>

                    Pembayaran Terakhir

                </h4>

                <small class="text-muted">

                    Riwayat transaksi pembayaran terbaru pelanggan

                </small>

            </div>

            <a href="{{ route('pembayaran.index') }}"
               class="btn btn-sm btn-outline-primary">

                Lihat Semua

            </a>

        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-hover mb-0">

                <thead class="bg-light">

                    <tr>

                        <th class="border-0">Invoice</th>

                        <th class="border-0">Pelanggan</th>

                        <th class="border-0 text-right">Nominal</th>

                        <th class="border-0 text-center">Metode</th>

                        <th class="border-0">Tanggal</th>

                        <th class="border-0 text-center">Status</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($pembayaranTerakhir as $item)

                        <tr>

                            <td>

                                <span class="font-weight-bold text-primary">

                                    {{ $item->invoice_no }}

                                </span>

                            </td>

                            <td>

                                {{ data_get($item, 'tagihan.pelanggan.nama', '-') }}

                            </td>

                            <td class="text-right font-weight-bold text-success">

                                Rp {{ number_format($item->total_bayar,0,',','.') }}

                            </td>

                            <td class="text-center">

                                @switch($item->metode)

                                    @case('Transfer')

                                        <span class="badge badge-info">

                                            Transfer

                                        </span>

                                        @break

                                    @case('Cash')

                                        <span class="badge badge-success">

                                            Cash

                                        </span>

                                        @break

                                    @case('Saldo')

                                        <span class="badge badge-primary">

                                            Saldo

                                        </span>

                                        @break

                                    @default

                                        <span class="badge badge-secondary">

                                            {{ $item->metode }}

                                        </span>

                                @endswitch

                            </td>

                            <td>

                                {{ optional($item->created_at)->format('d M Y H:i') }}

                            </td>

                            <td class="text-center">

                                @if($item->status == \App\Models\Pembayaran::STATUS_BERHASIL)

                                    <span class="badge badge-success">

                                        LUNAS

                                    </span>

                                @elseif($item->status == \App\Models\Pembayaran::STATUS_PENDING)

                                    <span class="badge badge-warning">

                                        PENDING

                                    </span>

                                @else

                                    <span class="badge badge-danger">

                                        DIBATALKAN

                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted py-4">

                                Belum ada pembayaran.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

    {{-- Audit Trail --}}
<div class="col-lg-5 mb-4">

    <div class="card dashboard-card h-100">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>

                <h4 class="font-weight-bold mb-1">

                    <i class="fas fa-history text-primary mr-2"></i>

                    Aktivitas Terbaru

                </h4>

                <small class="text-muted">

                    Riwayat aktivitas terbaru pada sistem

                </small>

            </div>

        </div>

        <div class="card-body p-0">

            <div class="list-group list-group-flush">

                @forelse($auditTerbaru as $audit)

                    <div class="list-group-item">

                        <div class="d-flex justify-content-between align-items-start">

                            <div class="flex-grow-1">

                                <strong class="text-primary">

                                    {{ $audit->module }}

                                </strong>

                                <br>

                                <small>

                                    {{ $audit->description }}

                                </small>

                            </div>

                            <small class="text-muted text-nowrap ml-3">

                                {{ optional($audit->created_at)->diffForHumans() ?? '-' }}

                            </small>

                        </div>

                    </div>

                @empty

                    <div class="list-group-item text-center text-muted py-4">

                        Belum ada aktivitas.

                    </div>

                @endforelse

            </div>

        </div>

    </div>

</div>

{{-- ========================================================= --}}
{{-- TAGIHAN JATUH TEMPO --}}
{{-- ========================================================= --}}

<div class="card dashboard-card mb-4">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="font-weight-bold mb-1">

                <i class="fas fa-calendar-times text-danger mr-2"></i>

                Tagihan Jatuh Tempo

            </h4>

            <small class="text-muted">

                Daftar tagihan yang telah melewati atau mendekati jatuh tempo

            </small>

        </div>

        <span class="badge badge-danger px-3 py-2">

            {{ count($tagihanJatuhTempo) }}

        </span>

    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-hover mb-0">

            <thead class="bg-light">

                <tr>

                    <th class="border-0">#</th>
                    <th class="border-0">Pelanggan</th>
                    <th class="border-0">Invoice</th>
                    <th class="border-0">Periode</th>
                    <th class="border-0">Jatuh Tempo</th>
                    <th class="border-0 text-right">Total</th>
                    <th class="border-0 text-center">Status</th>
                    <th class="border-0 text-center">Aksi</th>

                </tr>

            </thead>

            <tbody>

                @forelse($tagihanJatuhTempo as $i => $item)

                    <tr>

                        <td>{{ $i + 1 }}</td>

                        <td>
                            {{ data_get($item, 'pelanggan.nama', '-') }}
                        </td>

                        <td>
                            <span class="font-weight-bold text-primary">
                                {{ $item->invoice_no }}
                            </span>
                        </td>

                        <td>
                            {{ $item->periode }}
                        </td>

                        <td>
                            {{ optional($item->tanggal_jatuh_tempo)->format('d M Y') }}
                        </td>

                        <td class="text-right font-weight-bold text-danger">
                            Rp {{ number_format($item->total,0,',','.') }}
                        </td>

                        <td class="text-center">

                            @if($item->status == \App\Models\Tagihan::STATUS_JATUH_TEMPO)

                                <span class="badge badge-danger">
                                    JATUH TEMPO
                                </span>

                            @else

                                <span class="badge badge-warning">
                                    BELUM BAYAR
                                </span>

                            @endif

                        </td>

                        <td class="text-center">

                        <div class="btn-group btn-group-sm">

                            <a href="{{ route('tagihan.show', $item) }}"
                            class="btn btn-info"
                            title="Detail">

                                <i class="fas fa-eye"></i>

                            </a>

                            <a href="{{ route('tagihan.whatsapp', $item) }}"
                            class="btn btn-success"
                            title="Kirim WhatsApp">

                                <i class="fab fa-whatsapp"></i>

                            </a>

                        </div>

                    </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8"
                            class="text-center text-muted py-4">

                            Tidak ada tagihan jatuh tempo.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@stop
@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const dashboardChartData = {

    labels: @json($grafikLabel),

    datasets: [{

        data: @json($grafikPendapatan),

        borderColor: '#007bff',

        backgroundColor: 'rgba(0,123,255,.1)',

        fill: true

    }]

};

</script>

<script src="{{ asset('js/dashboard-enterprise.js') }}"></script>

@stop
