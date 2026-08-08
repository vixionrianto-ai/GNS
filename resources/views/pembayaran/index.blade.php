@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-3">
    <!-- Header Page Compact & Senada -->
    <div class="card border-0 shadow-sm rounded-3 mb-3 text-white overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div class="card-body px-3 py-2.5">
            <div class="row align-items-center g-2">
                <div class="col-md-7">
                    <span class="badge bg-white bg-opacity-25 text-white px-2.5 py-1 rounded-pill fw-semibold mb-1">
                        <i class="fas fa-shield-alt me-1"></i> GNS Billing System
                    </span>
                    <h5 class="fw-bold mb-0 text-white"><i class="fas fa-history me-2"></i> Riwayat Pembayaran</h5>
                </div>
                <div class="col-md-5 text-md-end">
                    <a href="{{ route('tagihan.index') }}" class="btn btn-light text-primary px-3 py-1.5 rounded-pill fw-bold shadow-sm small">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Data Tagihan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-primary border-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Pendapatan Hari Ini</span>
                        <h5 class="mb-0 fw-bold text-dark mt-1">Rp {{ number_format($totalHariIni ?? 0, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-2">
                        <i class="fas fa-money-bill-wave fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-success border-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Pendapatan Bulan Ini</span>
                        <h5 class="mb-0 fw-bold text-success mt-1">Rp {{ number_format($totalBulanIni ?? 0, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-2.5 rounded-2">
                        <i class="fas fa-wallet fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-warning border-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Total Transaksi</span>
                        <h5 class="mb-0 fw-bold text-dark mt-1">{{ $jumlahTransaksi ?? 0 }}</h5>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-2.5 rounded-2">
                        <i class="fas fa-receipt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-danger border-4 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 10px;">Pembayaran Berhasil</span>
                        <h5 class="mb-0 fw-bold text-danger mt-1">{{ $jumlahBerhasil ?? 0 }}</h5>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-2.5 rounded-2">
                        <i class="fas fa-check-circle fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Utama & Filter Section -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-2.5 px-3 border-0 d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary p-1.5 rounded-2 me-2">
                <i class="fas fa-table"></i>
            </div>
            <h6 class="mb-0 fw-bold text-dark">Data Riwayat Pembayaran</h6>
        </div>

        <!-- Form Filter Compact -->
        <div class="card-body bg-light bg-opacity-50 border-top border-bottom py-2.5 px-3">
            <form id="filterForm" method="GET" action="" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Periode</label>
                    <input type="text" name="periode" value="{{ request('periode') }}" class="form-control form-control-sm rounded-2 shadow-none py-1.5" placeholder="Contoh: 2026-07">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Status</label>
                    <select name="status" class="form-select form-select-sm rounded-2 shadow-none py-1.5">
                        <option value="">Semua Status</option>
                        <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Metode</label>
                    <select name="metode" class="form-select form-select-sm rounded-2 shadow-none py-1.5">
                        <option value="">Semua Metode</option>
                        <option value="Cash" {{ request('metode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Transfer" {{ request('metode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="QRIS" {{ request('metode') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">Cari Pelanggan / Invoice</label>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-2 shadow-none py-1.5" placeholder="Ketik nama / invoice...">
                </div>
                <div class="col-md-2 d-flex align-items-end mt-4">
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-2 fw-semibold py-1.5 shadow-sm">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase text-secondary" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3 py-2.5">No</th>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($pembayarans)
                        @forelse($pembayarans as $index => $item)
                        <tr>
                            <td class="ps-3 fw-semibold text-secondary">{{ $pembayarans->firstItem() + $index }}</td>
                            <td><span class="fw-bold text-dark">{{ $item->invoice_no ?? '-' }}</span></td>
                            <td>
                                <span class="fw-bold text-dark">{{ optional(optional($item->tagihan)->pelanggan)->nama ?? '-' }}</span>
                                <span class="text-muted small d-block" style="font-size: 10px;">{{ optional($item->tagihan)->periode ?? '-' }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1">{{ $item->metode ?? '-' }}</span></td>
                            <td><span class="fw-bold text-success">Rp {{ number_format($item->total_bayar ?? 0, 0, ',', '.') }}</span></td>
                            <td>
                                @php $status = strtolower(trim($item->status ?? '')); @endphp
                                @if($status == 'berhasil' || $status == 'success')
                                    <span class="badge bg-success text-white px-2 py-1 rounded-pill">Berhasil</span>
                                @elseif($status == 'pending')
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">Pending</span>
                                @else
                                    <span class="badge bg-danger text-white px-2 py-1 rounded-pill">{{ $item->status ?? '-' }}</span>
                                @endif
                            </td>
                            <td>{{ optional($item->tanggal_bayar)->format('d M Y') ?? '-' }}</td>
                            <td>{{ optional($item->user)->name ?? '-' }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('pembayaran.show', $item->id) }}" class="btn btn-sm btn-light text-primary border p-1 rounded-2" title="Detail">
                                        <i class="fas fa-eye fa-fw"></i>
                                    </a>
                                    <a href="{{ route('pembayaran.invoice', $item->id) }}" class="btn btn-sm btn-light text-success border p-1 rounded-2" title="Invoice">
                                        <i class="fas fa-receipt fa-fw"></i>
                                    </a>
                                    <a href="{{ route('pembayaran.pdf', $item->id) }}" class="btn btn-sm btn-light text-danger border p-1 rounded-2" title="PDF">
                                        <i class="fas fa-file-pdf fa-fw"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted small"><i class="fas fa-info-circle me-1"></i> Belum ada data riwayat pembayaran yang cocok.</div>
                            </td>
                        </tr>
                        @endforelse
                    @endisset
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination -->
        <div class="card-footer bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
            <span class="text-muted small" style="font-size: 11px;">Menampilkan riwayat transaksi pembayaran</span>
            @if(isset($pembayarans) && method_exists($pembayarans, 'links'))
                <div class="pagination-sm mb-0">{{ $pembayarans->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Script Live Search otomatis saat diketik -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let timeout = null;

        if(searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                // Jeda 400ms agar tidak terlalu sering melakukan submit saat mengetik cepat
                timeout = setTimeout(function() {
                    filterForm.submit();
                }, 400);
            });
        }
    });
</script>
@endsection