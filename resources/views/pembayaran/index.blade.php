@extends('adminlte::page')

@section('title', 'Riwayat Pembayaran')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">
        Riwayat Pembayaran
    </h1>
    <small class="text-muted" style="font-size: 11px;">
        Kelola seluruh transaksi pembayaran GNS Enterprise.
    </small>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3" style="font-size: 11px;">
    <i class="fas fa-check-circle mr-1"></i>
    {{ session('success') }}
    <button class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3" style="font-size: 11px;">
    <i class="fas fa-times-circle mr-1"></i>
    {{ session('error') }}
    <button class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<!-- Statistik -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #007bff !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">PENDAPATAN HARI INI</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 1.3rem;">Rp {{ number_format($totalHariIni ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px;height:40px;background-color:rgba(0,123,255,.1);">
                    <i class="fas fa-money-bill-wave text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">PENDAPATAN BULAN INI</span>
                    <h3 class="font-weight-bold mb-0 text-success" style="font-size: 1.3rem;">Rp {{ number_format($totalBulanIni ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(40,167,69,.1);">
                    <i class="fas fa-wallet text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">TOTAL TRANSAKSI</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 1.3rem;">{{ $jumlahTransaksi ?? 0 }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(255,193,7,.1);">
                    <i class="fas fa-receipt text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">PEMBAYARAN BERHASIL</span>
                    <h3 class="font-weight-bold mb-0 text-danger" style="font-size: 1.3rem;">{{ $jumlahBerhasil ?? 0 }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(220,53,69,.1);">
                    <i class="fas fa-check-circle text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Pembayaran -->
<div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-2">
    <div class="card-header bg-white py-2 border-0">
        <h3 class="card-title font-weight-bold text-dark m-0" style="font-size:.95rem;line-height:1.8;">
            <i class="fas fa-list text-primary mr-2"></i> Data Riwayat Pembayaran
        </h3>
        <div class="card-tools">
            <a href="{{ route('tagihan.index') }}" class="btn btn-sm btn-primary px-3 shadow-sm font-weight-bold">
                <i class="fas fa-file-invoice-dollar mr-1"></i> Data Tagihan
            </a>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-light border px-3 shadow-sm font-weight-bold ml-1">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card-body bg-light border-top border-bottom py-2 px-3">
        <form id="filterForm" method="GET" action="{{ route('pembayaran.index') }}" class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="form-label small font-weight-bold text-secondary mb-1" style="font-size:11px;">Periode</label>
                <input type="text" name="periode" value="{{ request('periode') }}" class="form-control form-control-sm rounded-2 shadow-none" placeholder="Contoh: 2026-07">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small font-weight-bold text-secondary mb-1" style="font-size:11px;">Status</label>
                <select name="status" class="form-control form-control-sm rounded-2 shadow-none">
                    <option value="">Semua Status</option>
                    <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label small font-weight-bold text-secondary mb-1" style="font-size:11px;">Metode</label>
                <select name="metode" class="form-control form-control-sm rounded-2 shadow-none">
                    <option value="">Semua Metode</option>
                    <option value="Cash" {{ request('metode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Transfer" {{ request('metode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="QRIS" {{ request('metode') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small font-weight-bold text-secondary mb-1" style="font-size:11px;">Cari Pelanggan / Invoice</label>
                <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-2 shadow-none" placeholder="Ketik nama / invoice...">
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 rounded-2 font-weight-bold shadow-sm">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0 text-sm">
            <thead class="bg-light">
                <tr>
                    <th width="50" class="text-center py-2">NO</th>
                    <th class="py-2">INVOICE</th>
                    <th class="py-2">PELANGGAN</th>
                    <th class="py-2">METODE</th>
                    <th class="py-2">TOTAL</th>
                    <th class="py-2">STATUS</th>
                    <th class="py-2">TANGGAL</th>
                    <th class="py-2">KASIR</th>
                    <th width="130" class="text-center py-2">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @isset($pembayarans)
                    @forelse($pembayarans as $index => $item)
                    <tr>
                        <td class="text-center py-2 align-middle">{{ method_exists($pembayarans, 'firstItem') ? $pembayarans->firstItem() + $index : $index + 1 }}</td>
                        <td class="py-2 align-middle"><strong class="text-dark">{{ $item->invoice_no ?? '-' }}</strong></td>
                        <td class="py-2 align-middle">
                            <strong class="text-dark">{{ optional(optional($item->tagihan)->pelanggan)->nama ?? '-' }}</strong>
                            <small class="text-muted d-block" style="font-size:10px;">{{ optional($item->tagihan)->periode ?? '-' }}</small>
                        </td>
                        <td class="py-2 align-middle"><span class="badge badge-light border px-2 py-1">{{ $item->metode ?? '-' }}</span></td>
                        <td class="py-2 align-middle"><strong class="text-success">Rp {{ number_format($item->total_bayar ?? 0, 0, ',', '.') }}</strong></td>
                        <td class="py-2 align-middle">
                            @php $status = strtolower(trim($item->status ?? '')); @endphp
                            @if($status == 'berhasil' || $status == 'success')
                                <span class="badge badge-success px-2 py-1">Berhasil</span>
                            @elseif($status == 'pending')
                                <span class="badge badge-warning px-2 py-1">Pending</span>
                            @else
                                <span class="badge badge-danger px-2 py-1">{{ $item->status ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="py-2 align-middle">{{ optional($item->tanggal_bayar)->format('d M Y') ?? '-' }}</td>
                        <td class="py-2 align-middle">{{ optional($item->user)->name ?? '-' }}</td>
                        <td class="text-center py-2 align-middle">
                            <div class="btn-group btn-group-sm shadow-sm">
                                <a href="{{ route('pembayaran.show', $item->id) }}" class="btn btn-light text-primary border" title="Detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('pembayaran.invoice', $item->id) }}" class="btn btn-light text-success border" title="Invoice"><i class="fas fa-receipt"></i></a>
                                <a href="{{ route('pembayaran.pdf', $item->id) }}" class="btn btn-light text-danger border" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4"><span class="text-muted small"><i class="fas fa-info-circle mr-1"></i> Belum ada data riwayat pembayaran yang cocok.</span></td>
                    </tr>
                    @endforelse
                @endisset
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-2 border-0">
        <div class="row align-items-center">
            <div class="col-md-6"><small class="text-muted" style="font-size:11px;">Menampilkan riwayat transaksi pembayaran</small></div>
            <div class="col-md-6 text-right">
                @if(isset($pembayarans) && method_exists($pembayarans, 'links'))
                    <div class="mb-0">{{ $pembayarans->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card { border-radius: 12px; }
.table td, .table th { vertical-align: middle; }
.badge { font-size: 11px; font-weight: 600; }
.btn { border-radius: 6px; }
.main-header.navbar { background-color:#ffffff !important; border-bottom:1px solid #dee2e6 !important; }
.main-header.navbar .nav-link { color:#343a40 !important; }
</style>
@stop

@section('js')
<script>
$(function(){
    $('[title]').tooltip();

    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let timeout = null;

    if (searchInput && filterForm) {
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () { filterForm.submit(); }, 400);
        });
    }
});
</script>
@stop