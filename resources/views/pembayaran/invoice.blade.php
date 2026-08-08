@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Page Compact & Senada -->
    <div class="card border-0 shadow-sm rounded-3 mb-2 text-white overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div class="card-body px-3 py-2">
            <div class="row align-items-center g-2">
                <div class="col-md-7">
                    <span class="badge bg-white bg-opacity-25 text-white px-2.5 py-1 rounded-pill fw-semibold mb-1">
                        <i class="fas fa-shield-alt me-1"></i> GNS Billing System
                    </span>
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-money-bill-wave me-2"></i> Invoice Pembayaran</h5>
                </div>
                <div class="col-md-5 text-md-end">
                    @if($pembayaran->tagihan->isLunas())
                        <span class="badge bg-success text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-1"></i> LUNAS
                        </span>
                    @elseif($pembayaran->tagihan->isSebagian())
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-coins me-1"></i> SEBAGIAN
                        </span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-exclamation-circle me-1"></i> BELUM LUNAS
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Utama 2 Kolom (Pas 1 Halaman) -->
    <div class="row g-2 mb-2">
        <!-- Kolom Kiri: Informasi Invoice & Pelanggan -->
        <div class="col-lg-8">
            <!-- Informasi Invoice -->
            <div class="card border-0 shadow-sm rounded-3 mb-2 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 border-0 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-1.5 rounded-2 me-2">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Informasi Invoice</h6>
                </div>
                <div class="card-body bg-light bg-opacity-50 border-top py-2 px-3">
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Nomor Invoice</span>
                            <span class="badge bg-primary px-2 py-1">{{ $pembayaran->invoice_no ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Tanggal Pembayaran</span>
                            <span class="fw-bold text-dark">{{ optional($pembayaran->tanggal_bayar)->format('d F Y') ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Metode Pembayaran</span>
                            <span class="fw-semibold text-dark">{{ $pembayaran->metode ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Kasir</span>
                            <span class="fw-semibold text-dark">{{ optional($pembayaran->user)->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Pelanggan -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 border-0 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-1.5 rounded-2 me-2">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Informasi Pelanggan</h6>
                </div>
                <div class="card-body bg-light bg-opacity-50 border-top py-2 px-3">
                    <div class="row g-2 small">
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Nama Pelanggan</span>
                            <span class="fw-bold text-dark">{{ optional(optional($pembayaran->tagihan)->pelanggan)->nama ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Nomor HP</span>
                            <span class="fw-semibold text-dark">{{ optional(optional($pembayaran->tagihan)->pelanggan)->no_hp ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Username PPPoE</span>
                            <span class="fw-semibold text-primary">{{ optional(optional($pembayaran->tagihan)->pelanggan)->username_pppoe ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block fw-semibold">Alamat</span>
                            <span class="text-dark">{{ optional(optional($pembayaran->tagihan)->pelanggan)->alamat ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Total & Ringkasan -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-2 h-100">
                <!-- Card Total Pembayaran -->
                <div class="p-3 rounded-3 text-white shadow-sm d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                    <div>
                        <span class="text-white-50 fw-bold text-uppercase small" style="font-size: 10px;">Total Pembayaran</span>
                        <h4 class="fw-bold mb-0 mt-0.5">Rp {{ number_format($pembayaran->total_bayar ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-circle" style="width: 42px; height: 42px; min-width: 42px;">
                        <i class="fas fa-wallet fs-5 text-white"></i>
                    </div>
                </div>

                <!-- Card Ringkasan -->
                <div class="card border-0 shadow-sm rounded-3 flex-grow-1 overflow-hidden">
                    <div class="card-header bg-white py-2 px-3 border-0">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-success me-2"></i> Ringkasan</h6>
                    </div>
                    <div class="card-body bg-light bg-opacity-50 border-top py-2 px-3">
                        <table class="table table-borderless align-middle mb-0 small">
                            <tr>
                                <td class="text-muted py-1.5">Periode</td>
                                <td class="fw-semibold text-dark text-end py-1.5">{{ optional($pembayaran->tagihan)->periode ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-1.5">Router</td>
                                <td class="fw-semibold text-dark text-end py-1.5">{{ optional(optional(optional($pembayaran->tagihan)->pelanggan)->router)->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-1.5">Paket</td>
                                <td class="fw-semibold text-primary text-end py-1.5">{{ optional(optional(optional($pembayaran->tagihan)->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold text-dark py-2">Dibayar</td>
                                <td class="fw-bold text-success text-end py-2">Rp {{ number_format($pembayaran->dibayar ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-1.5">Kembalian</td>
                                <td class="fw-bold text-primary text-end py-1.5">Rp {{ number_format($pembayaran->kembalian ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi Bawah -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <a href="{{ route('pembayaran.show', $pembayaran) }}" class="btn btn-secondary btn-sm px-3 rounded-pill fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('pembayaran.pdf', $pembayaran) }}" class="btn btn-danger btn-sm px-3 rounded-pill fw-bold shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </a>
                <button type="button" onclick="window.print()" class="btn btn-primary btn-sm px-3 rounded-pill fw-bold shadow-sm">
                    <i class="fas fa-print me-1"></i> Cetak
                </button>
                @if(isset($waUrl) && $waUrl)
                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success btn-sm px-3 rounded-pill fw-bold shadow-sm">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection