@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header Page Compact & Normal Font -->
    <div class="card border-0 shadow-sm rounded-3 mb-2 text-white overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div class="card-body px-3 py-2">
            <div class="row align-items-center g-2">
                <div class="col-md-7">
                    <span class="badge bg-white bg-opacity-25 text-white px-2.5 py-1 rounded-pill fw-semibold mb-1">
                        <i class="fas fa-shield-alt me-1"></i> GNS Billing System
                    </span>
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-money-bill-wave me-2"></i> Pembayaran Tagihan</h5>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="{{ route('tagihan.index') }}" class="btn btn-light text-primary px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout Utama 2 Kolom -->
    <div class="row g-2">
        <!-- Kolom Kiri: Informasi Pelanggan & Form Pembayaran -->
        <div class="col-lg-8">
            <!-- Informasi Pelanggan -->
            <div class="card border-0 shadow-sm rounded-3 mb-2 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 border-0 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary p-1.5 rounded-2 me-2">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Informasi Pelanggan</h6>
                </div>
                <div class="card-body bg-light bg-opacity-50 border-top py-2 px-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <span class="text-muted d-block small fw-semibold">Nama Pelanggan</span>
                            <span class="fw-bold text-dark">{{ optional($tagihan->pelanggan)->nama ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block small fw-semibold">Invoice</span>
                            <span class="fw-bold text-dark">{{ $tagihan->invoice_no ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block small fw-semibold">Paket Internet</span>
                            <span class="fw-semibold text-primary">{{ optional(optional($tagihan->pelanggan)->paket)->nama_paket ?? '-' }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block small fw-semibold">Router / No HP</span>
                            <span class="fw-semibold text-dark">{{ optional(optional($tagihan->pelanggan)->router)->nama_router ?? '-' }} / {{ optional($tagihan->pelanggan)->no_hp ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Pembayaran -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 border-0 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success p-1.5 rounded-2 me-2">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Form Pembayaran</h6>
                </div>
                <div class="card-body bg-white border-top py-2 px-3">
                    <form id="formPembayaran" action="{{ route('pembayaran.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Tanggal Bayar</label>
                                <input type="date" name="tanggal_bayar" class="form-control form-control-sm rounded-2 shadow-none py-1.5" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Metode Pembayaran</label>
                                <select name="metode" class="form-select form-select-sm rounded-2 shadow-none py-1.5" required>
                                    <option value="Cash">Cash / Tunai</option>
                                    <option value="Transfer">Transfer Bank</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Nominal Dibayar (Rp)</label>
                                <!-- Input bertipe text dengan format titik -->
                                <input type="text" id="inputDibayar" name="dibayar" class="form-control form-control-sm rounded-2 shadow-none py-1.5" value="{{ number_format($totalSisaTagihan ?? 0, 0, ',', '.') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Biaya Admin (Rp)</label>
                                <input type="text" id="inputAdmin" name="biaya_admin" class="form-control form-control-sm rounded-2 shadow-none py-1.5" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary mb-1">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control form-control-sm rounded-2 shadow-none py-1.5" placeholder="Opsional">
                            </div>
                            <div class="col-12 mt-2 text-end">
                                <button type="submit" class="btn btn-success btn-sm px-4 py-1.5 rounded-pill fw-bold shadow-sm">
                                    <i class="fas fa-check-circle me-1"></i> Simpan Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Total & Ringkasan Tagihan -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-2 h-100">
                <!-- Card Total Tagihan -->
                <div class="p-3 rounded-3 text-white shadow-sm d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                    <div>
                        <span class="text-white-50 fw-bold text-uppercase small" style="font-size: 10px;">Total ({{ $tagihan->periode ?? '-' }})</span>
                        <h4 class="fw-bold mb-0 mt-0.5">Rp {{ number_format($tagihan->total ?? 0, 0, ',', '.') }}</h4>
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
                                <td class="text-muted py-1.5">Nominal</td>
                                <td class="fw-semibold text-dark text-end py-1.5">Rp {{ number_format($tagihan->nominal ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-1.5">Denda</td>
                                <td class="fw-semibold text-danger text-end py-1.5">Rp {{ number_format($tagihan->denda ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold text-dark py-2">Total</td>
                                <td class="fw-bold text-success text-end py-2">Rp {{ number_format($tagihan->total ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-1.5">Status</td>
                                <td class="text-end py-1.5">
                                    @php $status = strtolower(trim($tagihan->status ?? '')); @endphp
                                    @if($status == 'lunas' || $status == 'paid')
                                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill">Lunas</span>
                                    @elseif($status == 'jatuh tempo' || $status == 'overdue')
                                        <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill">Jatuh Tempo</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill">{{ $tagihan->status ?? 'Belum Bayar' }}</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script Format Titik & Handler Tombol Simpan -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputDibayar = document.getElementById('inputDibayar');
        const inputAdmin = document.getElementById('inputAdmin');
        const form = document.getElementById('formPembayaran');

        function formatRupiah(el) {
            let val = el.value.replace(/[^0-9]/g, '');
            if (val) {
                el.value = new Intl.NumberFormat('id-ID').format(val);
            } else {
                el.value = '';
            }
        }

        if(inputDibayar) {
            inputDibayar.addEventListener('input', function() { formatRupiah(this); });
        }
        if(inputAdmin) {
            inputAdmin.addEventListener('input', function() { formatRupiah(this); });
        }

        if(form) {
            form.addEventListener('submit', function() {
                if(inputDibayar) inputDibayar.value = inputDibayar.value.replace(/\./g, '');
                if(inputAdmin) inputAdmin.value = inputAdmin.value.replace(/\./g, '');
            });
        }
    });
</script>
@endsection