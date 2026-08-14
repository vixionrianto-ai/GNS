@extends('adminlte::page')

@section('title', 'Detail Tagihan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    <div>
        <h1 class="mb-1 font-weight-bold text-dark" style="font-size:1.35rem;">
            <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>
            Detail Tagihan
        </h1>
        <small class="text-muted" style="font-size:11px;">
            Informasi lengkap tagihan pelanggan GNS Network.
        </small>
    </div>

    <ol class="breadcrumb float-sm-right mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('tagihan.index') }}">Tagihan</a>
        </li>
        <li class="breadcrumb-item active">Detail</li>
    </ol>
</div>
@stop

@section('content')

@php
    $status = strtolower(trim($tagihan->status ?? ''));
    $total = (float) ($tagihan->total ?? 0);
    $dibayar = (float) ($tagihan->dibayar ?? 0);
    $sisa = max(0, $total - $dibayar);
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-file-invoice mr-2"></i>
                    {{ $tagihan->invoice_no ?? '-' }}
                </h3>

                <div class="card-tools">
                    @if($status !== 'lunas' && $status !== 'paid' && Route::has('pembayaran.create'))
                        <a href="{{ route('pembayaran.create', $tagihan->id) }}" class="btn btn-success btn-sm mr-1">
                            <i class="fas fa-money-bill-wave mr-1"></i> Bayar Tagihan
                        </a>
                    @endif

                    @if(Route::has('tagihan.whatsapp'))
                        <a href="{{ route('tagihan.whatsapp', $tagihan->id) }}" class="btn btn-success btn-sm mr-1" title="Kirim WhatsApp">
                            <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                        </a>
                    @endif

                    <a href="{{ route('tagihan.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="small text-muted text-uppercase font-weight-bold">Pelanggan</div>
                        <h5 class="font-weight-bold mb-0">
                            {{ optional($tagihan->pelanggan)->nama ?? '-' }}
                        </h5>
                    </div>

                    <div class="col-md-6 mb-3 text-md-right">
                        <div class="small text-muted text-uppercase font-weight-bold">Periode</div>
                        <h5 class="font-weight-bold mb-0">{{ $tagihan->periode ?? '-' }}</h5>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="info-box shadow-sm mb-0">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total</span>
                                <span class="info-box-number">Rp {{ number_format($total,0,',','.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="info-box shadow-sm mb-0">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dibayar</span>
                                <span class="info-box-number">Rp {{ number_format($dibayar,0,',','.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="info-box shadow-sm mb-0">
                            <span class="info-box-icon bg-danger">
                                <i class="fas fa-wallet"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sisa</span>
                                <span class="info-box-number">Rp {{ number_format($sisa,0,',','.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="info-box shadow-sm mb-0">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jatuh Tempo</span>
                                <span class="info-box-number" style="font-size:13px;">
                                    {{ $tagihan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d M Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-user-circle text-primary mr-2"></i>
                                    Informasi Pelanggan
                                </h3>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Nama</th>
                                        <td>{{ optional($tagihan->pelanggan)->nama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>No. HP / WhatsApp</th>
                                        <td>{{ optional($tagihan->pelanggan)->no_hp ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Paket Internet</th>
                                        <td>{{ optional(optional($tagihan->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td>{{ optional($tagihan->pelanggan)->alamat ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-file-invoice text-success mr-2"></i>
                                    Ringkasan Tagihan
                                </h3>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Invoice</th>
                                        <td>{{ $tagihan->invoice_no ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Periode</th>
                                        <td>{{ $tagihan->periode ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Tagihan</th>
                                        <td>{{ $tagihan->tanggal_tagihan ? \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d M Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jatuh Tempo</th>
                                        <td class="text-danger font-weight-bold">{{ $tagihan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d M Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($status === 'lunas' || $status === 'paid')
                                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Lunas</span>
                                            @elseif($status === 'sebagian' || $status === 'partial')
                                                <span class="badge badge-warning px-2 py-1"><i class="fas fa-adjust mr-1"></i>Sebagian</span>
                                            @elseif($status === 'jatuh tempo' || $status === 'overdue')
                                                <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>Jatuh Tempo</span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i>{{ $tagihan->status ?? 'Belum Bayar' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($tagihan->keterangan))
                    <div class="alert alert-light border mt-2 mb-0">
                        <strong><i class="fas fa-info-circle text-primary mr-1"></i> Keterangan:</strong>
                        {{ $tagihan->keterangan }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-success card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-money-check-alt mr-2"></i>
                    Status Pembayaran
                </h3>
            </div>
            <div class="card-body text-center">
                @if($status === 'lunas' || $status === 'paid')
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h4 class="font-weight-bold text-success">LUNAS</h4>
                    <p class="text-muted mb-0">Tagihan ini sudah dibayar.</p>
                @elseif($status === 'sebagian' || $status === 'partial')
                    <i class="fas fa-adjust fa-4x text-warning mb-3"></i>
                    <h4 class="font-weight-bold text-warning">SEBAGIAN</h4>
                    <p class="text-muted">Masih terdapat sisa tagihan.</p>
                    <h4 class="font-weight-bold text-danger">Rp {{ number_format($sisa,0,',','.') }}</h4>
                @elseif($status === 'jatuh tempo' || $status === 'overdue')
                    <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                    <h4 class="font-weight-bold text-danger">JATUH TEMPO</h4>
                    <p class="text-muted">Tagihan belum diselesaikan sampai jatuh tempo.</p>
                    <h4 class="font-weight-bold text-danger">Rp {{ number_format($sisa,0,',','.') }}</h4>
                @else
                    <i class="fas fa-clock fa-4x text-secondary mb-3"></i>
                    <h4 class="font-weight-bold text-secondary">{{ strtoupper($tagihan->status ?? 'BELUM BAYAR') }}</h4>
                    <p class="text-muted">Tagihan menunggu pembayaran.</p>
                    <h4 class="font-weight-bold text-danger">Rp {{ number_format($sisa,0,',','.') }}</h4>
                @endif
            </div>
        </div>

        <div class="card card-warning card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-bolt mr-2"></i>
                    Aksi
                </h3>
            </div>
            <div class="card-body">
                @if($status !== 'lunas' && $status !== 'paid' && Route::has('pembayaran.create'))
                    <a href="{{ route('pembayaran.create', $tagihan->id) }}" class="btn btn-success btn-block">
                        <i class="fas fa-money-bill-wave mr-2"></i> Proses Pembayaran
                    </a>
                @endif

                @if(Route::has('tagihan.whatsapp'))
                    <a href="{{ route('tagihan.whatsapp', $tagihan->id) }}" class="btn btn-success btn-block">
                        <i class="fab fa-whatsapp mr-2"></i> Kirim WhatsApp
                    </a>
                @endif

                <a href="{{ route('tagihan.index') }}" class="btn btn-secondary btn-block mb-0">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Tagihan
                </a>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .card { border-radius: 12px; }
    .info-box { min-height: 74px; }
    .info-box .info-box-icon { width: 60px; }
    .info-box-number { font-size: 13px; white-space: normal; }
    .table td, .table th { vertical-align: middle; }
    .btn { border-radius: 6px; }
    .badge { font-size: 11px; font-weight: 600; }
    .main-header.navbar { background-color:#fff !important; border-bottom:1px solid #dee2e6 !important; }
    .main-header.navbar .nav-link { color:#343a40 !important; }
</style>
@stop
