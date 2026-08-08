@extends('adminlte::page')

@section('title', 'Dashboard Enterprise')

@section('css')

<link rel="stylesheet" href="{{ asset('css/gns.css') }}">

<style>

:root{

    --radius:18px;

    --shadow:0 10px 28px rgba(0,0,0,.08);

    --shadow-hover:0 16px 35px rgba(0,0,0,.12);

}

body{

    background:#f4f6fb;

}

.dashboard-card{

    border:0;

    border-radius:var(--radius);

    box-shadow:var(--shadow);

    overflow:hidden;

}

.dashboard-card .card-header{

    background:#fff;

    border-bottom:1px solid #eef2f7;

    padding:18px 22px;

}

.kpi-card{

    border:0;

    border-radius:var(--radius);

    box-shadow:var(--shadow);

    transition:.25s;

    height:170px;

}

.kpi-card:hover{

    transform:translateY(-4px);

    box-shadow:var(--shadow-hover);

}

.kpi-card .card-body{

    height:100%;

    display:flex;

    flex-direction:column;

    justify-content:space-between;

    padding:22px;

}

.kpi-top{

    display:flex;

    justify-content:space-between;

    align-items:flex-start;

}

.kpi-icon{

    width:64px;

    height:64px;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#fff;

    font-size:24px;

    flex-shrink:0;

}

.small-title{

    font-size:12px;

    font-weight:700;

    letter-spacing:.8px;

    color:#6c757d;

    text-transform:uppercase;

}

.kpi-value{

    font-size:44px;

    font-weight:700;

    line-height:1;

    margin-top:8px;

    margin-bottom:8px;

}

.kpi-money{

    font-size:36px;

    font-weight:700;

    line-height:1.15;

    white-space:nowrap;

}

.kpi-desc{

    font-size:13px;

    min-height:20px;

}

.progress-thin{

    height:5px;

    border-radius:20px;

}

.quick-btn{

    height:95px;

    border-radius:12px;

    display:flex;

    flex-direction:column;

    justify-content:center;

    align-items:center;

    font-weight:600;

}

.header-title{

    font-size:34px;

    font-weight:700;

}

.header-subtitle{

    color:#6c757d;

    font-size:15px;

}

</style>

@stop


@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap">

    <div>

        <div class="header-title">

            Dashboard GNS Enterprise

        </div>

        <div class="header-subtitle">

            Billing Management System • Monitoring • MikroTik • Statistik

        </div>

    </div>

    <div class="text-lg-right mt-3 mt-lg-0">

        <div class="font-weight-bold">

            {{ Auth::user()->name }}

        </div>

        <small class="text-muted">

            Administrator

        </small>

        <div class="text-primary mt-2">

            {{ now()->translatedFormat('l, d F Y') }}

        </div>

        <div class="font-weight-bold">

            <span id="clock"></span>

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
                        {{ number_format($pelangganAktif) }} Aktif
                    </div>

                </div>

                <div class="kpi-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>

            </div>

            <div class="progress progress-thin">

                <div class="progress-bar bg-primary"
                     style="width:100%">
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
                        Hari ini :
                        Rp {{ number_format($pendapatanHariIni,0,',','.') }}
                    </div>

                </div>

                <div class="kpi-icon bg-success">
                    <i class="fas fa-wallet"></i>
                </div>

            </div>

            <div class="progress progress-thin">

                <div class="progress-bar bg-success"
                     style="width:100%">
                </div>

            </div>

        </div>

    </div>

</div>

{{-- Belum Lunas --}}
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

                <div class="kpi-icon bg-danger">
                    <i class="fas fa-file-invoice"></i>
                </div>

            </div>

            <div class="progress progress-thin">

                <div class="progress-bar bg-danger"
                     style="width:100%">
                </div>

            </div>

        </div>

    </div>

</div>

{{-- Collection --}}
<div class="col-xl-3 col-lg-6 col-md-6 mb-4">

    <div class="card kpi-card">

        <div class="card-body">

            <div class="kpi-top">

                <div>

                    <div class="small-title">
                        COLLECTION RATE
                    </div>

                    <div class="kpi-value">
                        {{ $collectionRate }}%
                    </div>

                    <div class="kpi-desc text-success">

                        {{ number_format($tagihanLunas) }}

                        dari

                        {{ number_format($totalTagihan) }}

                        Tagihan

                    </div>

                </div>

                <div class="kpi-icon bg-info">
                    <i class="fas fa-chart-pie"></i>
                </div>

            </div>

            <div class="progress progress-thin">

                <div class="progress-bar bg-info"
                     style="width:{{ $collectionRate }}%">
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
<div class="col-lg-3 col-md-6 mb-4">

    <div class="card kpi-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <div class="small-title">
                        TAGIHAN LUNAS
                    </div>

                    <h2 class="font-weight-bold mb-1">
                        {{ number_format($tagihanLunas) }}
                    </h2>

                    <small class="text-success">
                        Sudah Dibayar
                    </small>

                </div>

                <div class="kpi-icon bg-success">

                    <i class="fas fa-check-circle"></i>

                </div>

            </div>

            <div class="progress progress-thin mt-3">

                <div class="progress-bar bg-success"
                     style="width:100%">
                </div>

            </div>

        </div>

    </div>

</div>

    {{-- Tagihan Sebagian --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>                   
                        <div class="small-title">
                            TAGIHAN SEBAGIAN
                        </div>

                        <h2 class="font-weight-bold mb-1">
                            {{ number_format($tagihanSebagian) }}
                        </h2>

                        <small class="text-warning">
                            Pembayaran Bertahap
                        </small>

                    </div>

                    <div class="kpi-icon bg-warning">

                        <i class="fas fa-percentage"></i>

                    </div>

                </div>

                <div class="progress progress-thin mt-3">

                    <div class="progress-bar bg-warning"
                         style="width:100%">

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Total Piutang --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="small-title">
                            TOTAL PIUTANG
                        </div>

                        <h5 class="font-weight-bold mb-1 text-nowrap">
                            Rp {{ number_format($totalPiutang,0,',','.') }}
                        </h5>

                        <small class="text-danger">
                            Belum Tertagih
                        </small>

                    </div>

                    <div class="kpi-icon bg-danger">

                        <i class="fas fa-hand-holding-usd"></i>

                    </div>

                </div>

                <div class="progress progress-thin mt-3">

                    <div class="progress-bar bg-danger"
                         style="width:100%">

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Saldo Pelanggan --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card kpi-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="small-title">
                            SALDO PELANGGAN
                        </div>

                        <h5 class="font-weight-bold mb-1 text-nowrap">
                            Rp {{ number_format($totalSaldoPelanggan,0,',','.') }}
                        </h5>

                        <small class="text-success">
                            Deposit Pelanggan
                        </small>

                    </div>

                    <div class="kpi-icon bg-success">

                        <i class="fas fa-wallet"></i>

                    </div>

                </div>

                <div class="progress progress-thin mt-3">

                    <div class="progress-bar bg-success"
                         style="width:100%">

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- ========================================================= --}}
{{-- GRAFIK + QUICK ACTION --}}
{{-- ========================================================= --}}

<div class="row">

    <div class="col-lg-8 mb-4">

        <div class="card dashboard-card">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center">

                    <h3 class="card-title mb-0">

                        <i class="fas fa-chart-line text-primary mr-2"></i>

                        Grafik Pendapatan 12 Bulan

                    </h3>

                    <span class="badge badge-success">

                        {{ now()->year }}

                    </span>

                </div>

            </div>

            <div class="card-body">

                <canvas id="incomeChart"
                        height="120"></canvas>

            </div>

        </div>

    </div>

    <div class="col-lg-4 mb-4">

        <div class="card dashboard-card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-bolt text-warning mr-2"></i>

                    Quick Action

                </h3>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-6 mb-3">

                        <a href="{{ route('pelanggan.create') }}"
                           class="btn btn-primary btn-block quick-btn">

                            <i class="fas fa-user-plus"></i>

                            <br>

                            Pelanggan

                        </a>

                    </div>

                    <div class="col-6 mb-3">

                        <a href="{{ route('paket.create') }}"
                           class="btn btn-primary btn-block quick-btn">

                            <i class="fas fa-wifi"></i>

                            <br>

                            Paket

                        </a>

                    </div>

                    <div class="col-6">

                        <a href="{{ route('tagihan.index') }}"
                           class="btn btn-primary btn-block quick-btn">

                            <i class="fas fa-file-invoice"></i>

                            <br>

                            Tagihan

                        </a>

                    </div>

                    <div class="col-6">

                        <a href="{{ route('pembayaran.index') }}"
                           class="btn btn-primary btn-block quick-btn">

                            <i class="fas fa-money-check-alt"></i>

                            <br>

                            Pembayaran

                        </a>

                    </div>

                </div>

                <hr>

                <table class="table table-sm mb-0">

                    <tr>

                        <td>Total Pendapatan</td>

                        <td class="text-right">

                            <strong>

                                Rp {{ number_format($totalPendapatan,0,',','.') }}

                            </strong>

                        </td>

                    </tr>

                    <tr>

                        <td>Total Pembayaran</td>

                        <td class="text-right">

                            {{ number_format($totalPembayaran) }}

                        </td>

                    </tr>

                    <tr>

                        <td>Tagihan Hari Ini</td>

                        <td class="text-right">

                            {{ number_format($tagihanHariIni) }}

                        </td>

                    </tr>

                    <tr>

                        <td>Router Aktif</td>

                        <td class="text-right">

                            {{ number_format($routerAktif) }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>
{{-- ========================================================= --}}
{{-- STATISTIK • STATUS SISTEM • MIKROTIK --}}
{{-- ========================================================= --}}

<div class="row">

    {{-- Statistik Tagihan --}}
    <div class="col-lg-4 mb-4">

        <div class="card dashboard-card h-100">

            <div class="card-header">

                <h3 class="card-title mb-0">

                    <i class="fas fa-file-invoice text-primary mr-2"></i>

                    Statistik Tagihan

                </h3>

            </div>

            <div class="card-body p-0">

                <div class="info-box border-0 mb-0">

                    <span class="info-box-icon bg-success">

                        <i class="fas fa-check-circle"></i>

                    </span>

                    <div class="info-box-content">

                        <span class="info-box-text">

                            Tagihan Lunas

                        </span>

                        <span class="info-box-number">

                            {{ number_format($tagihanLunas) }}

                        </span>

                    </div>

                </div>

                <div class="info-box border-0 mb-0">

                    <span class="info-box-icon bg-danger">

                        <i class="fas fa-times-circle"></i>

                    </span>

                    <div class="info-box-content">

                        <span class="info-box-text">

                            Belum Dibayar

                        </span>

                        <span class="info-box-number">

                            {{ number_format($tagihanBelumBayar) }}

                        </span>

                    </div>

                </div>

                <div class="info-box border-0">

                    <span class="info-box-icon bg-info">

                        <i class="fas fa-calendar-day"></i>

                    </span>

                    <div class="info-box-content">

                        <span class="info-box-text">

                            Tagihan Hari Ini

                        </span>

                        <span class="info-box-number">

                            {{ number_format($tagihanHariIni) }}

                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Status Sistem --}}
    <div class="col-lg-4 mb-4">

        <div class="card dashboard-card h-100">

            <div class="card-header">

                <h3 class="card-title mb-0">

                    <i class="fas fa-server text-success mr-2"></i>

                    Status Sistem

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-sm table-borderless mb-0">

                    <tr>

                        <td>Versi GNS</td>

                        <td class="text-right">

                            <strong>Enterprise v4</strong>

                        </td>

                    </tr>

                    <tr>

                        <td>Laravel</td>

                        <td class="text-right">

                            {{ app()->version() }}

                        </td>

                    </tr>

                    <tr>

                        <td>Status</td>

                        <td class="text-right">

                            <span class="badge badge-success">

                                ONLINE

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <td>Jam Server</td>

                        <td class="text-right">

                            <span id="serverClock"></span>

                        </td>

                    </tr>

                    <tr>

                        <td>Total Router</td>

                        <td class="text-right">

                            {{ number_format($totalRouter) }}

                        </td>

                    </tr>

                    <tr>

                        <td>Pelanggan Aktif</td>

                        <td class="text-right">

                            {{ number_format($pelangganAktif) }}

                        </td>

                    </tr>

                    <tr>

                        <td>Tagihan Lunas</td>

                        <td class="text-right">

                            {{ number_format($tagihanLunas) }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>


    {{-- Monitoring MikroTik --}}
    <div class="col-lg-4 mb-4">

        <div class="card dashboard-card h-100">

            <div class="card-header">

                <h3 class="card-title mb-0">

                    <i class="fas fa-network-wired text-info mr-2"></i>

                    Monitoring MikroTik

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-sm table-borderless mb-0">

                    <tr>

                        <td>Status</td>

                        <td class="text-right">

                            @if($mikrotikStatus)

                                <span class="badge badge-success">

                                    ONLINE

                                </span>

                            @else

                                <span class="badge badge-danger">

                                    OFFLINE

                                </span>

                            @endif

                        </td>

                    </tr>

                    <tr>

                        <td>Identity</td>

                        <td class="text-right">

                            {{ $routerIdentity }}

                        </td>

                    </tr>

                    <tr>

                        <td>Version</td>

                        <td class="text-right">

                            {{ $routerVersion }}

                        </td>

                    </tr>

                    <tr>

                        <td>CPU Load</td>

                        <td class="text-right">

                            {{ $routerCpu }}%

                        </td>

                    </tr>

                    <tr>

                        <td>Memory</td>

                        <td class="text-right">

                            {{ $routerMemory }}

                        </td>

                    </tr>

                    <tr>

                        <td>Uptime</td>

                        <td class="text-right">

                            {{ $routerUptime }}

                        </td>

                    </tr>

                    <tr>

                        <td>PPP Active</td>

                        <td class="text-right">

                            {{ number_format($pppActive) }}

                        </td>

                    </tr>

                    <tr>

                        <td>PPP Secret</td>

                        <td class="text-right">

                            {{ number_format($pppSecret) }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

{{-- ========================================================= --}}
{{-- PEMBAYARAN TERAKHIR + AUDIT TRAIL --}}
{{-- ========================================================= --}}

<div class="row">

    {{-- Pembayaran Terakhir --}}
    <div class="col-lg-7 mb-4">

        <div class="card dashboard-card">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center">

                    <h3 class="card-title mb-0">

                        <i class="fas fa-money-check-alt text-success mr-2"></i>

                        Pembayaran Terakhir

                    </h3>

                    <a href="{{ route('pembayaran.index') }}"
                       class="btn btn-sm btn-outline-primary">

                        Lihat Semua

                    </a>

                </div>

            </div>

            <div class="card-body table-responsive p-0">

                <table class="table table-hover mb-0">

                    <thead>

                        <tr>

                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($pembayaranTerakhir as $item)

                        <tr>

                            <td>
                                <strong>{{ $item->invoice_no }}</strong>
                            </td>

                            <td>
                                {{ data_get($item, 'tagihan.pelanggan.nama', '-') }}
                            </td>

                            <td>
                                Rp {{ number_format($item->total_bayar,0,',','.') }}
                            </td>
                            <td>

                                @switch($item->metode)

                                    @case('Cash')
                                        <span class="badge badge-success">Cash</span>
                                        @break

                                    @case('Transfer')
                                        <span class="badge badge-primary">Transfer</span>
                                        @break

                                    @case('Saldo')
                                        <span class="badge badge-warning">Saldo</span>
                                        @break

                                    @default
                                        <span class="badge badge-secondary">
                                            {{ $item->metode }}
                                        </span>

                                @endswitch

                            </td>
                            <td>
                                {{ optional($item->tanggal_bayar)->format('d M Y H:i') }}
                            </td>

                            <td>

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

        <div class="card dashboard-card">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center">

                    <h3 class="card-title mb-0">

                        <i class="fas fa-history text-primary mr-2"></i>

                        Aktivitas Terbaru

                    </h3>

                </div>

            </div>

            <div class="card-body p-0">

                <div class="list-group list-group-flush">

                    @forelse($auditTerbaru as $audit)

                        <div class="list-group-item">

                            <strong>

                                {{ $audit->module }}

                            </strong>

                            <br>

                            <small>

                                {{ $audit->description }}

                            </small>

                            <br>

                            <small class="text-muted">

                                {{ optional($audit->created_at)->diffForHumans() ?? '-' }}

                            </small>

                        </div>

                    @empty

                        <div class="list-group-item text-center text-muted">

                            Belum ada aktivitas.

                        </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- TAGIHAN JATUH TEMPO --}}
{{-- ========================================================= --}}

<div class="card dashboard-card">

    <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">

            <h3 class="card-title mb-0">

                <i class="fas fa-calendar-times text-danger mr-2"></i>

                Tagihan Jatuh Tempo

            </h3>

            <span class="badge badge-danger">

                {{ count($tagihanJatuhTempo) }}

            </span>

        </div>

    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-hover mb-0">

            <thead>

                <tr>

                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Invoice</th>
                    <th>Periode</th>
                    <th>Jatuh Tempo</th>
                    <th>Total</th>

                    <th>Status</th>

                </tr>

            </thead>

            <tbody>

                @forelse($tagihanJatuhTempo as $i => $item)

                <tr>

                    <td>{{ $i+1 }}</td>

                    <td>{{ data_get($item, 'pelanggan.nama', '-') }}</td>

                    <td>{{ $item->invoice_no }}</td>

                    <td>{{ $item->periode }}</td>

                    <td>{{ optional($item->tanggal_jatuh_tempo)->format('d M Y') }}</td>

                    <td>

                        Rp {{ number_format($item->total,0,',','.') }}

                    </td>

                    <td>

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

                </tr>

                @empty

                <tr>

                    <td colspan="7"
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

document.addEventListener('DOMContentLoaded', function () {

    function updateClock() {

        const now = new Date();

        const jam = now.toLocaleTimeString('id-ID');

        const tanggal = now.toLocaleString('id-ID');

        document.getElementById('clock').innerHTML = jam;

        document.getElementById('serverClock').innerHTML = tanggal;

    }

    updateClock();

    setInterval(updateClock,1000);

    const ctx = document.getElementById('incomeChart');

    if(ctx){

        new Chart(ctx,{

            type:'line',

            data:{
                labels:@json($grafikLabel),
                datasets:[{
                    data:@json($grafikPendapatan),
                    borderColor:'#007bff',
                    backgroundColor:'rgba(0,123,255,.1)',
                    fill:true,
                    tension:.35
                }]
            },

            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    legend:{display:false}
                }
            }

        });

    }

});

</script>

@stop