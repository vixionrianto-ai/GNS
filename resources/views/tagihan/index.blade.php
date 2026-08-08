@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Tagihan</h4>
            <p class="text-muted small mb-0">Kelola seluruh tagihan pelanggan GNS Network dengan mudah dan terstruktur.</p>
        </div>
    </div>

    <!-- Statistik Cards Bootstrap -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Tagihan</span>
                        <h3 class="mb-0 fw-bold text-dark mt-1">{{ $totalTagihan ?? 0 }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="fas fa-file-invoice-dollar fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Sudah Lunas</span>
                        <h3 class="mb-0 fw-bold text-success mt-1">{{ $totalLunas ?? 0 }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="fas fa-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Belum Bayar</span>
                        <h3 class="mb-0 fw-bold text-warning mt-1">{{ $totalBelumBayar ?? 0 }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                        <i class="fas fa-clock fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Jatuh Tempo</span>
                        <h3 class="mb-0 fw-bold text-danger mt-1">{{ $totalJatuhTempo ?? 0 }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                        <i class="fas fa-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Utama & Filter Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-table text-primary me-2"></i> Daftar Tagihan</h5>
            
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('tagihan.generate.semua') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill fw-semibold"><i class="fas fa-bolt me-1"></i> Generate Semua</button>
                </form>
                <form action="{{ route('tagihan.generate') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill fw-semibold"><i class="fas fa-calendar-day me-1"></i> Generate Hari Ini</button>
                </form>
                <button type="button" class="btn btn-sm btn-info text-white px-3 rounded-pill fw-semibold" data-bs-toggle="modal" data-bs-target="#modalGeneratePeriode"><i class="fas fa-calendar-alt me-1"></i> Generate Periode</button>
                <a href="{{ route('tagihan.index') }}" class="btn btn-sm btn-light border px-3 rounded-pill fw-semibold"><i class="fas fa-sync-alt me-1"></i> Refresh</a>
            </div>
        </div>

        <!-- Form Filter -->
        <div class="card-body bg-light border-top border-bottom py-3">
            <form id="filterTagihanForm" method="GET" action="{{ route('tagihan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Periode</label>
                    <input type="text" name="periode" value="{{ request('periode') }}" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Pilih Periode">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm rounded-3 shadow-none" onchange="this.form.submit()">
                        <option value="Semua" {{ request('status') == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="Belum Bayar" {{ request('status') == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="Jatuh Tempo" {{ request('status') == 'Jatuh Tempo' ? 'selected' : '' }}>Jatuh Tempo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Cari Pelanggan</label>
                    <input type="text" id="searchTagihanInput" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-3 shadow-none" placeholder="Nama atau nomor invoice...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100 rounded-3 fw-semibold py-1.5 shadow-sm"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="ps-4 py-3">No</th>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Periode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($tagihans)
                        @forelse($tagihans as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">{{ method_exists($tagihans, 'firstItem') ? $tagihans->firstItem() + $index : $index + 1 }}</td>
                            <td><span class="fw-bold text-dark">{{ $item->invoice_no ?? '-' }}</span></td>
                            <td>{{ optional($item->pelanggan)->nama ?? '-' }}</td>
                            <td>{{ optional(optional($item->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                            <td>{{ $item->periode ?? '-' }}</td>
                            <td>Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
                            <td>
                                @php $status = strtolower(trim($item->status ?? '')); @endphp
                                @if($status == 'lunas' || $status == 'paid')
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill">Lunas</span>
                                @elseif($status == 'jatuh tempo' || $status == 'overdue')
                                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill">Jatuh Tempo</span>
                                @else
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">{{ $item->status ?? 'Belum Bayar' }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <a href="{{ Route::has('tagihan.show') ? route('tagihan.show', $item->id) : '#' }}" class="btn btn-sm btn-light text-primary border-0" title="Detail Tagihan">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(Route::has('pembayaran.create'))
                                        <a href="{{ route('pembayaran.create', $item->id) }}" class="btn btn-sm btn-light text-success border-0" title="Proses Pembayaran">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </a>
                                    @endif

                                    <form action="{{ route('tagihan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tagihan ini beserta data pembayarannya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger border-0" title="Hapus Tagihan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <div class="text-primary bg-primary bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                        <i class="fas fa-folder-open fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-0">Tidak ada data tagihan yang cocok dengan kata kunci atau filter Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-4">
                                    <div class="text-primary bg-primary bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                        <i class="fas fa-folder-open fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-0">Silakan ubah kata kunci pencarian atau filter untuk melihat data tagihan.</p>
                                </div>
                            </td>
                        </tr>
                    @endisset
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination -->
        <div class="card-footer bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <span class="text-muted small">Menampilkan data tagihan sistem</span>
            @if(isset($tagihans) && method_exists($tagihans, 'links'))
                <div>{{ $tagihans->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Generate Periode -->
<div class="modal fade" id="modalGeneratePeriode" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('tagihan.generate.periode') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
            @csrf
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-calendar-alt text-primary me-2"></i> Generate Tagihan Periode</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Pilih Periode (Bulan & Tahun) <span class="text-danger">*</span></label>
                    <input type="month" name="periode" class="form-control rounded-3 shadow-none py-2" value="{{ date('Y-m') }}" required>
                </div>

                <div class="mb-1">
                    <label class="form-label fw-bold small text-secondary">Pilih Pelanggan (Opsional)</label>
                    <select name="pelanggan_id" class="form-select rounded-3 shadow-none py-2">
                        <option value="">-- Semua Pelanggan (Massal) --</option>
                        @if(isset($pelanggans))
                            @foreach($pelanggans as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->kode_pelanggan }})</option>
                            @endforeach
                        @endif
                    </select>
                    <small class="text-muted d-block mt-2" style="font-size: 11px;">Kosongkan jika ingin generate tagihan untuk seluruh pelanggan aktif sekaligus.</small>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2">
                <button type="button" class="btn btn-light border btn-sm px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill fw-semibold shadow-sm">Generate</button>
            </div>
        </form>
    </div>
</div>

<!-- Script Live Search Otomatis -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchTagihanInput');
        const filterForm = document.getElementById('filterTagihanForm');
        let timeout = null;

        if(searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    filterForm.submit();
                }, 400);
            });
        }
    });
</script>
@endsection