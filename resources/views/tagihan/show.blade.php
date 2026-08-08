@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Page Lebih Compact dengan Tombol Bayar & Kembali -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 text-white overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-7">
                    <span class="badge bg-white bg-opacity-25 text-white px-2 py-1 rounded-pill fw-semibold mb-1" style="font-size: 10px;">
                        <i class="fas fa-shield-alt me-1"></i> Billing Management System
                    </span>
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-file-invoice-dollar me-2"></i> Invoice: {{ $tagihan->invoice_no ?? '-' }}</h5>
                </div>
                <div class="col-md-5 text-md-end d-flex justify-content-md-end gap-2">
                    @php $status = strtolower(trim($tagihan->status ?? '')); @endphp
                    @if($status != 'lunas' && $status != 'paid')
                        <a href="{{ Route::has('pembayaran.create') ? route('pembayaran.create', $tagihan->id) : '#' }}" class="btn btn-success btn-sm px-3 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-money-bill-wave me-1"></i> Bayar Tagihan
                        </a>
                    @endif
                    <a href="{{ route('tagihan.index') }}" class="btn btn-light text-primary btn-sm px-3 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Ringkasan Utama Compact -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
        <div class="card-body p-3 bg-white">
            <div class="row align-items-center g-2 mb-3 pb-2 border-bottom">
                <div class="col-md-6">
                    <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 11px;">Status Pembayaran</span>
                    <div>
                        @if($status == 'lunas' || $status == 'paid')
                            <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm"><i class="fas fa-check-circle me-1"></i> Lunas</span>
                        @elseif($status == 'jatuh tempo' || $status == 'overdue')
                            <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm"><i class="fas fa-exclamation-triangle me-1"></i> Jatuh Tempo</span>
                        @else
                            <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-sm"><i class="fas fa-clock me-1"></i> {{ $tagihan->status ?? 'Belum Bayar' }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 11px;">Pelanggan & Periode</span>
                    <h6 class="fw-bold text-dark mb-0">{{ optional($tagihan->pelanggan)->nama ?? '-' }} <span class="badge bg-light text-secondary border fw-normal py-1">Periode: {{ $tagihan->periode ?? '-' }}</span></h6>
                </div>
            </div>

            <!-- Financial Stats Grid dengan Warna Berbeda -->
            <div class="row g-2">
                <!-- Total Tagihan (Soft Blue) -->
                <div class="col-xl-3 col-md-6">
                    <div class="p-3 rounded-3 border border-primary border-opacity-25 shadow-sm h-100" style="background-color: #f0f7ff;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-primary fw-bold text-uppercase" style="font-size: 10px;">Total Tagihan</span>
                                <h5 class="fw-bold text-dark mb-0 mt-1">Rp {{ number_format($tagihan->total ?? 0, 0, ',', '.') }}</h5>
                            </div>
                            <div class="text-primary bg-primary bg-opacity-10 p-2 rounded-3">
                                <i class="fas fa-file-invoice-dollar fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sudah Dibayar (Soft Green) -->
                <div class="col-xl-3 col-md-6">
                    <div class="p-3 rounded-3 border border-success border-opacity-25 shadow-sm h-100" style="background-color: #f1f8f3;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-success fw-bold text-uppercase" style="font-size: 10px;">Sudah Dibayar</span>
                                <h5 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($tagihan->dibayar ?? 0, 0, ',', '.') }}</h5>
                            </div>
                            <div class="text-success bg-success bg-opacity-10 p-2 rounded-3">
                                <i class="fas fa-check-circle fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sisa Tagihan (Soft Red) -->
                <div class="col-xl-3 col-md-6">
                    <div class="p-3 rounded-3 border border-danger border-opacity-25 shadow-sm h-100" style="background-color: #fdf2f2;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-danger fw-bold text-uppercase" style="font-size: 10px;">Sisa Tagihan</span>
                                <h5 class="fw-bold text-danger mb-0 mt-1">Rp {{ number_format(($tagihan->total - ($tagihan->dibayar ?? 0)) ?? 0, 0, ',', '.') }}</h5>
                            </div>
                            <div class="text-danger bg-danger bg-opacity-10 p-2 rounded-3">
                                <i class="fas fa-wallet fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jatuh Tempo (Soft Yellow/Amber) -->
                <div class="col-xl-3 col-md-6">
                    <div class="p-3 rounded-3 border border-warning border-opacity-25 shadow-sm h-100" style="background-color: #fef9e7;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-warning text-dark fw-bold text-uppercase" style="font-size: 10px;">Jatuh Tempo</span>
                                <h6 class="fw-bold text-dark mb-0 mt-1">{{ Str::limit($tagihan->tanggal_jatuh_tempo ?? '-', 10, '') }}</h6>
                            </div>
                            <div class="text-warning bg-warning bg-opacity-10 p-2 rounded-3">
                                <i class="fas fa-calendar-alt fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Informasi Dua Kolom Compact -->
    <div class="row g-3">
        <!-- Informasi Pelanggan -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 border-0 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-1.5 rounded-2 me-2">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Informasi Pelanggan</h6>
                </div>
                <div class="card-body bg-light bg-opacity-50 border-top py-2 px-3">
                    <table class="table table-borderless align-middle mb-0 small">
                        <tr>
                            <td class="text-muted w-45 py-1.5 fw-semibold">Nama Pelanggan</td>
                            <td class="fw-bold text-dark py-1.5">: {{ optional($tagihan->pelanggan)->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5 fw-semibold">No. HP / WhatsApp</td>
                            <td class="fw-semibold text-dark py-1.5">: {{ optional($tagihan->pelanggan)->no_hp ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5 fw-semibold">Paket Internet</td>
                            <td class="fw-semibold text-primary py-1.5">: {{ optional(optional($tagihan->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5 fw-semibold">Alamat</td>
                            <td class="text-dark py-1.5">: {{ optional($tagihan->pelanggan)->alamat ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ringkasan Tagihan -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 border-0 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-1.5 rounded-2 me-2">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Ringkasan Tagihan</h6>
                </div>
                <div class="card-body bg-light bg-opacity-50 border-top py-2 px-3">
                    <table class="table table-borderless align-middle mb-0 small">
                        <tr>
                            <td class="text-muted w-45 py-1.5 fw-semibold">Periode Tagihan</td>
                            <td class="fw-bold text-dark py-1.5">: {{ $tagihan->periode ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5 fw-semibold">Tanggal Tagihan</td>
                            <td class="fw-semibold text-dark py-1.5">: {{ Str::limit($tagihan->tanggal_tagihan ?? '-', 10, '') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5 fw-semibold">Jatuh Tempo</td>
                            <td class="fw-semibold text-danger py-1.5">: {{ Str::limit($tagihan->tanggal_jatuh_tempo ?? '-', 10, '') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1.5 fw-semibold">Keterangan</td>
                            <td class="text-dark py-1.5">: {{ $tagihan->keterangan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection