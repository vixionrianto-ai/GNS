@extends('adminlte::page')

@section('title', 'Detail Pelanggan')

@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-user-circle text-primary"></i>

            Detail Pelanggan

        </h1>

        <small class="text-muted">

            Informasi lengkap pelanggan GNS Network beserta status layanan internet.

        </small>

    </div>

    <ol class="breadcrumb float-sm-right mb-0">

        <li class="breadcrumb-item">

            <a href="{{ route('dashboard') }}">

                <i class="fas fa-home"></i>

                Dashboard

            </a>

        </li>

        <li class="breadcrumb-item">

            <a href="{{ route('pelanggan.index') }}">

                Pelanggan

            </a>

        </li>

        <li class="breadcrumb-item active">

            Detail

        </li>

    </ol>

</div>

@stop


@section('content')

<div class="row">

    <div class="col-lg-4">

        <div class="card card-primary card-outline shadow-sm">

            <div class="card-body text-center">

                <div class="mb-3">

                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white shadow"
                         style="width:110px;height:110px;font-size:42px;font-weight:bold;">

                        {{ strtoupper(substr($pelanggan->nama,0,1)) }}

                    </div>

                </div>

                <h3 class="mb-1">

                    {{ $pelanggan->nama }}

                </h3>

                <p class="text-muted">

                    {{ $pelanggan->kode_pelanggan }}

                </p>

                @if($pelanggan->status=='Aktif')

                    <span class="badge badge-success px-3 py-2">

                        <i class="fas fa-check-circle"></i>

                        Aktif

                    </span>

                @else

                    <span class="badge badge-danger px-3 py-2">

                        <i class="fas fa-times-circle"></i>

                        Non Aktif

                    </span>

                @endif

                <hr>

                <div class="text-left">

                    <p>

                        <i class="fas fa-phone text-success mr-2"></i>

                        {{ $pelanggan->no_hp }}

                    </p>

                    <p>

                        <i class="fas fa-calendar-check text-primary mr-2"></i>

                        {{ $pelanggan->tanggal_aktif ?? '-' }}

                    </p>

                    <p>

                        <i class="fas fa-map-marker-alt text-danger mr-2"></i>

                        {{ $pelanggan->alamat }}

                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-8">

        <div class="card card-success card-outline shadow-sm">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-network-wired"></i>

                    Informasi Internet

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-striped table-bordered">
                    <tr>

                        <th width="230">

                            <i class="fas fa-server text-primary mr-2"></i>

                            Router MikroTik

                        </th>

                        <td>

                            <span class="badge badge-secondary px-3 py-2">

                                <i class="fas fa-network-wired mr-1"></i>

                                {{ $pelanggan->router->nama ?? '-' }}

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-wifi text-info mr-2"></i>

                            Paket Internet

                        </th>

                        <td>

                            <span class="badge badge-info px-3 py-2">

                                {{ $pelanggan->paket->nama_paket ?? '-' }}

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-user text-success mr-2"></i>

                            Username PPPoE

                        </th>

                        <td>

                            <code class="px-2 py-1">

                                {{ $pelanggan->username_pppoe }}

                            </code>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-lock text-danger mr-2"></i>

                            Password PPPoE

                        </th>

                        <td>

                            <span class="text-muted">

                                ••••••••

                            </span>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-network-wired text-warning mr-2"></i>

                            IP Address

                        </th>

                        <td>

                            {{ $pelanggan->ip_address ?: '-' }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-ethernet text-secondary mr-2"></i>

                            MAC Address

                        </th>

                        <td>

                            {{ $pelanggan->mac_address ?: '-' }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-calendar-plus text-primary mr-2"></i>

                            Tanggal Pasang

                        </th>

                        <td>

                            {{ $pelanggan->tanggal_pasang ?: '-' }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <i class="fas fa-calendar-check text-success mr-2"></i>

                            Tanggal Aktif

                        </th>

                        <td>

                            {{ $pelanggan->tanggal_aktif ?: '-' }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>
<div class="row">

    {{-- RIWAYAT TAGIHAN --}}
    <div class="col-lg-7">

        <div class="card card-warning card-outline shadow-sm">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-file-invoice-dollar"></i>

                    Riwayat Tagihan

                </h3>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="bg-light">

                            <tr>

                                <th>Invoice</th>

                                <th>Periode</th>

                                <th class="text-right">Total</th>

                                <th class="text-center">Status</th>

                                <th class="text-center">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        @forelse($pelanggan->tagihans as $tagihan)

                            <tr>

                                <td>

                                    <strong class="text-primary">

                                        {{ $tagihan->invoice_no }}

                                    </strong>

                                </td>

                                <td>

                                    {{ $tagihan->periode }}

                                </td>

                                <td class="text-right font-weight-bold">

                                    Rp {{ number_format($tagihan->total,0,',','.') }}

                                </td>

                                <td class="text-center">

                                    @switch($tagihan->status)

                                        @case('Lunas')

                                            <span class="badge badge-success px-3 py-2">

                                                <i class="fas fa-check-circle"></i>

                                                Lunas

                                            </span>

                                            @break

                                        @case('Sebagian')

                                            <span class="badge badge-warning text-dark px-3 py-2">

                                                <i class="fas fa-adjust"></i>

                                                Sebagian

                                            </span>

                                            @break

                                        @case('Jatuh Tempo')

                                            <span class="badge badge-danger px-3 py-2">

                                                <i class="fas fa-exclamation-triangle"></i>

                                                Jatuh Tempo

                                            </span>

                                            @break

                                        @case('Belum Bayar')

                                            <span class="badge badge-secondary px-3 py-2">

                                                <i class="fas fa-clock"></i>

                                                Belum Bayar

                                            </span>

                                            @break

                                        @default

                                            <span class="badge badge-secondary px-3 py-2">

                                                {{ $tagihan->status }}

                                            </span>

                                    @endswitch

                                </td>

                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('tagihan.show', $tagihan->id) }}" class="btn btn-info btn-sm" title="Detail Tagihan">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($tagihan->status != 'Lunas')
                                            <a href="{{ route('pembayaran.create', $tagihan->id) }}" class="btn btn-success btn-sm" title="Form Pembayaran">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center py-5">

                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>

                                    <br>

                                    <span class="text-muted">

                                        Belum ada riwayat tagihan.

                                    </span>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    {{-- RIWAYAT PEMBAYARAN --}}
    <div class="col-lg-5">

        <div class="card card-success card-outline shadow-sm">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-money-check-alt"></i>

                    Riwayat Pembayaran

                </h3>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="bg-light">

                            <tr>

                                <th>Invoice</th>

                                <th>Total</th>

                                <th>Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            @php($adaPembayaran = false)

                            @foreach($pelanggan->tagihans as $tagihan)

                                @if($tagihan->pembayaran)

                                    @php($adaPembayaran = true)

                                    <tr>

                                        <td>

                                            <strong>

                                                {{ $tagihan->pembayaran->invoice_no }}

                                            </strong>

                                        </td>

                                        <td>

                                            Rp {{ number_format($tagihan->pembayaran->total_bayar,0,',','.') }}

                                        </td>

                                        <td>

                                            <span class="badge badge-success px-3 py-2">

                                                <i class="fas fa-check"></i>

                                                {{ $tagihan->pembayaran->status }}

                                            </span>

                                        </td>

                                    </tr>

                                @endif

                            @endforeach

                            @if(!$adaPembayaran)

                                <tr>

                                    <td colspan="3" class="text-center py-5">

                                        <i class="fas fa-wallet fa-3x text-muted mb-3"></i>

                                        <br>

                                        <span class="text-muted">

                                            Belum ada riwayat pembayaran.

                                        </span>

                                    </td>

                                </tr>

                            @endif

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>
<div class="row">

    <div class="col-12">

        <div class="card card-primary card-outline shadow-sm">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-network-wired"></i>

                    Status MikroTik

                </h3>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-lg-3 col-md-6">

                        <div class="info-box shadow-sm">

                            <span class="info-box-icon bg-success">

                                <i class="fas fa-check-circle"></i>

                            </span>

                            <div class="info-box-content">

                                <span class="info-box-text">

                                    Status

                                </span>

                                <span class="info-box-number">

                                    {{ $pelanggan->status }}

                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 col-md-6">

                        <div class="info-box shadow-sm">

                            <span class="info-box-icon bg-info">

                                <i class="fas fa-user"></i>

                            </span>

                            <div class="info-box-content">

                                <span class="info-box-text">

                                    PPP Secret

                                </span>

                                <span class="info-box-number">

                                    {{ $pelanggan->username_pppoe }}

                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 col-md-6">

                        <div class="info-box shadow-sm">

                            <span class="info-box-icon bg-warning">

                                <i class="fas fa-wifi"></i>

                            </span>

                            <div class="info-box-content">

                                <span class="info-box-text">

                                    Paket

                                </span>

                                <span class="info-box-number">

                                    {{ $pelanggan->paket->nama_paket ?? '-' }}

                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 col-md-6">

                        <div class="info-box shadow-sm">

                            <span class="info-box-icon bg-danger">

                                <i class="fas fa-server"></i>

                            </span>

                            <div class="info-box-content">

                                <span class="info-box-text">

                                    Router

                                </span>

                                <span class="info-box-number">

                                    {{ $pelanggan->router->nama ?? '-' }}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<div class="card shadow-sm">

    <div class="card-body text-center">

        <a href="{{ route('pelanggan.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

        <a href="{{ route('pelanggan.edit', $pelanggan->id) }}"
           class="btn btn-warning">

            <i class="fas fa-edit"></i>

            Edit

        </a>

        @if($pelanggan->tagihans->where('status', '!=', 'Lunas')->first())
            <a href="{{ route('pembayaran.create', $pelanggan->tagihans->where('status', '!=', 'Lunas')->first()->id) }}"
               class="btn btn-success">

                <i class="fas fa-money-bill-wave"></i>

                Pembayaran

            </a>
        @else
            <a href="{{ route('pembayaran.index') }}"
               class="btn btn-success">

                <i class="fas fa-money-bill-wave"></i>

                Pembayaran

            </a>
        @endif

    </div>

</div>

@stop


@section('css')

<style>

.card{
    border-radius:12px;
    box-shadow:0 .125rem .25rem rgba(0,0,0,.075);
}

.card-header{
    border-bottom:1px solid #ececec;
}

.info-box{
    border-radius:12px;
    transition:.25s;
}

.info-box:hover{
    transform:translateY(-3px);
    box-shadow:0 .5rem 1rem rgba(0,0,0,.12);
}

.table th{
    width:230px;
    white-space:nowrap;
    background:#f8f9fa;
}

.table td,
.table th{
    vertical-align:middle;
}

.badge{
    font-size:12px;
    font-weight:600;
}

code{
    color:#0d6efd;
    background:#eef5ff;
    padding:4px 8px;
    border-radius:4px;
}

.btn{
    border-radius:8px;
}

.profile-avatar{
    box-shadow:0 10px 25px rgba(13,110,253,.25);
}

</style>

@stop


@section('js')

<script>

$(function(){

    $('[data-toggle="tooltip"]').tooltip();

});

</script>

@stop