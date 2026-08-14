@extends('adminlte::page')

@section('title', 'Data Tagihan')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">Data Tagihan</h1>
    <small class="text-muted" style="font-size: 11px;">Kelola seluruh tagihan pelanggan GNS Network dengan mudah dan terstruktur.</small>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3" style="font-size: 11px;">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3" style="font-size: 11px;">
    <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
    <button type="button" class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #007bff !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">TOTAL TAGIHAN</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size:1.3rem;">{{ $totalTagihan ?? 0 }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(0,123,255,.1);">
                    <i class="fas fa-file-invoice-dollar text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #28a745 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">SUDAH LUNAS</span>
                    <h3 class="font-weight-bold mb-0 text-success" style="font-size:1.3rem;">{{ $totalLunas ?? 0 }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(40,167,69,.1);">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #ffc107 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">BELUM BAYAR</span>
                    <h3 class="font-weight-bold mb-0 text-warning" style="font-size:1.3rem;">{{ $totalBelumBayar ?? 0 }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(255,193,7,.1);">
                    <i class="fas fa-clock text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">JATUH TEMPO</span>
                    <h3 class="font-weight-bold mb-0 text-danger" style="font-size:1.3rem;">{{ $totalJatuhTempo ?? 0 }}</h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background-color:rgba(220,53,69,.1);">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-2">
    <div class="card-header bg-white py-2 border-0">
        <h3 class="card-title font-weight-bold text-dark m-0" style="font-size:.95rem;line-height:1.8;">
            <i class="fas fa-file-invoice text-primary mr-2"></i>Daftar Tagihan
        </h3>
        <div class="card-tools d-flex align-items-center flex-wrap">
            <form action="{{ route('tagihan.generate.semua') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm font-weight-bold">
                    <i class="fas fa-bolt mr-1"></i>Generate Semua
                </button>
            </form>
            <form action="{{ route('tagihan.generate') }}" method="POST" class="d-inline ml-1">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm font-weight-bold">
                    <i class="fas fa-calendar-day mr-1"></i>Generate Hari Ini
                </button>
            </form>
            <button type="button" class="btn btn-sm btn-info text-white px-3 shadow-sm font-weight-bold ml-1" data-toggle="modal" data-target="#modalGeneratePeriode">
                <i class="fas fa-calendar-alt mr-1"></i>Generate Periode
            </button>
            <a href="{{ route('tagihan.index') }}" class="btn btn-sm btn-light border px-3 shadow-sm font-weight-bold ml-1">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
            </a>
        </div>
    </div>

    <div class="card-body bg-light border-top border-bottom py-2">
        <form id="filterTagihanForm" method="GET" action="{{ route('tagihan.index') }}" class="row align-items-end">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-secondary mb-1">Periode</label>
                <input type="text" name="periode" value="{{ request('periode') }}" class="form-control form-control-sm" placeholder="Pilih Periode">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="small font-weight-bold text-secondary mb-1">Status</label>
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="Semua" {{ request('status') == 'Semua' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                    <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="Belum Bayar" {{ request('status') == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="Jatuh Tempo" {{ request('status') == 'Jatuh Tempo' ? 'selected' : '' }}>Jatuh Tempo</option>
                </select>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <label class="small font-weight-bold text-secondary mb-1">Cari Pelanggan</label>
                <input type="text" id="searchTagihanInput" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Nama atau nomor invoice...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary btn-block font-weight-bold">
                    <i class="fas fa-search mr-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0 text-sm">
            <thead class="bg-light">
                <tr>
                    <th width="60" class="text-center py-2">NO</th>
                    <th class="py-2">INVOICE</th>
                    <th class="py-2">PELANGGAN</th>
                    <th class="py-2">PAKET</th>
                    <th class="py-2">PERIODE</th>
                    <th class="py-2">TOTAL</th>
                    <th class="py-2">STATUS</th>
                    <th width="140" class="text-center py-2">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tagihans as $index => $item)
                @php $status = strtolower(trim($item->status ?? '')); @endphp
                <tr>
                    <td class="text-center py-2 align-middle">{{ $tagihans->firstItem() + $index }}</td>
                    <td class="py-2 align-middle"><strong class="text-dark">{{ $item->invoice_no ?? '-' }}</strong></td>
                    <td class="py-2 align-middle">{{ optional($item->pelanggan)->nama ?? '-' }}</td>
                    <td class="py-2 align-middle">{{ optional(optional($item->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                    <td class="py-2 align-middle">{{ $item->periode ?? '-' }}</td>
                    <td class="py-2 align-middle"><strong>Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}</strong></td>
                    <td class="py-2 align-middle">
                        @if($status === 'lunas' || $status === 'paid')
                            <span class="badge badge-success px-2 py-1">Lunas</span>
                        @elseif($status === 'jatuh tempo' || $status === 'overdue')
                            <span class="badge badge-danger px-2 py-1">Jatuh Tempo</span>
                        @else
                            <span class="badge badge-warning px-2 py-1">{{ $item->status ?? 'Belum Bayar' }}</span>
                        @endif
                    </td>
                    <td class="text-center py-2 align-middle">
                        <div class="btn-group btn-group-sm shadow-sm">
                            <a href="{{ route('tagihan.show', $item->id) }}" class="btn btn-info btn-sm" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(Route::has('pembayaran.create'))
                            <a href="{{ route('pembayaran.create', $item->id) }}" class="btn btn-success btn-sm" title="Proses Pembayaran">
                                <i class="fas fa-money-bill-wave"></i>
                            </a>
                            @endif
                            <form action="{{ route('tagihan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tagihan ini beserta data pembayarannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm ml-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i><br>
                        <span class="text-muted small">Data tagihan tidak ditemukan.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-2 border-0">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted" style="font-size:11px;">Menampilkan data tagihan sistem</small>
            </div>
            <div class="col-md-6 text-right">
                {{ $tagihans->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGeneratePeriode" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="{{ route('tagihan.generate.periode') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-2"></i>Generate Tagihan Periode</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="small font-weight-bold">Pilih Periode <span class="text-danger">*</span></label>
                    <input type="month" name="periode" class="form-control" value="{{ date('Y-m') }}" required>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Pilih Pelanggan (Opsional)</label>
                    <select name="pelanggan_id" class="form-control">
                        <option value="">-- Semua Pelanggan (Massal) --</option>
                        @foreach($pelanggans as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->kode_pelanggan }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Kosongkan untuk seluruh pelanggan aktif.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Generate</button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
.card { border-radius: 12px; }
.table td, .table th { vertical-align: middle; }
.badge { font-size: 11px; font-weight: 600; }
.btn { border-radius: 6px; }
.main-header.navbar { background-color:#fff !important; border-bottom:1px solid #dee2e6 !important; }
.main-header.navbar .nav-link { color:#343a40 !important; }
</style>
@stop

@section('js')
<script>
$(function(){
    $('[title]').tooltip();
    const searchInput = $('#searchTagihanInput');
    let timeout = null;
    searchInput.on('input', function(){
        clearTimeout(timeout);
        timeout = setTimeout(function(){ $('#filterTagihanForm').submit(); }, 400);
    });
});
</script>
@stop