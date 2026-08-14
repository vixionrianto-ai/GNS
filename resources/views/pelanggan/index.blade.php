@extends('adminlte::page')

@section('title', 'Pelanggan')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">Data Pelanggan</h1>
    <small class="text-muted" style="font-size: 11px;">Kelola seluruh pelanggan GNS Network, sinkronisasi MikroTik, dan status layanan pelanggan.</small>
</div>
@stop

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3">
    <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
    <button class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3">
    <i class="fas fa-times-circle mr-1"></i>{{ session('error') }}
    <button class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3">
    <ul class="mb-0 pl-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #007bff !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div><span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">TOTAL PELANGGAN</span><h3 class="font-weight-bold mb-0 text-dark" style="font-size:1.3rem;">{{ $totalPelanggan }}</h3></div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(0,123,255,.1);"><i class="fas fa-users text-primary"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #28a745 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div><span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">PELANGGAN AKTIF</span><h3 class="font-weight-bold mb-0 text-success" style="font-size:1.3rem;">{{ $pelangganAktif }}</h3></div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(40,167,69,.1);"><i class="fas fa-user-check text-success"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div><span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">NON AKTIF</span><h3 class="font-weight-bold mb-0 text-danger" style="font-size:1.3rem;">{{ $pelangganNonAktif }}</h3></div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(220,53,69,.1);"><i class="fas fa-user-slash text-danger"></i></div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left:4px solid #ffc107 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div><span class="text-muted small font-weight-bold text-uppercase" style="font-size:9px;">PAKET INTERNET</span><h3 class="font-weight-bold mb-0 text-warning" style="font-size:1.3rem;">{{ $totalPaket }}</h3></div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(255,193,7,.1);"><i class="fas fa-wifi text-warning"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-2">
    <div class="card-header bg-white py-2 border-0 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size:.95rem;"><i class="fas fa-users text-primary mr-2"></i>Daftar Pelanggan</h3>
            <small class="d-block text-muted mt-1" style="font-size:11px;">Daftar seluruh pelanggan yang terhubung dengan sistem GNS Network.</small>
        </div>
        <div class="card-tools mt-1 mt-md-0">
            <a href="{{ route('pelanggan.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm font-weight-bold"><i class="fas fa-user-plus mr-1"></i>Tambah Pelanggan</a>
            <form action="{{ route('pelanggan.sync') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm font-weight-bold ml-1"><i class="fas fa-sync mr-1"></i>Sinkron MikroTik</button>
            </form>
            <a href="{{ route('pelanggan.index') }}" class="btn btn-sm btn-light border px-3 shadow-sm font-weight-bold ml-1"><i class="fas fa-sync-alt mr-1"></i>Refresh</a>
        </div>
    </div>

    <div class="card-body bg-light border-top border-bottom py-2">
        <form method="GET" action="{{ route('pelanggan.index') }}" class="row align-items-center">
            <div class="col-md-5 col-lg-4">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama pelanggan, kode, atau no HP...">
                    <button type="submit" class="btn btn-primary px-3"><i class="fas fa-search mr-1"></i>Cari</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0 text-sm">
            <thead class="bg-light">
                <tr>
                    <th width="55" class="text-center py-2">NO</th>
                    <th class="py-2">KODE</th>
                    <th class="py-2">PELANGGAN</th>
                    <th class="py-2">NO. HP</th>
                    <th class="py-2">ROUTER</th>
                    <th class="py-2">PAKET</th>
                    <th class="py-2">USERNAME PPPOE</th>
                    <th class="py-2">STATUS</th>
                    <th width="130" class="text-center py-2">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggans as $index => $item)
                <tr>
                    <td class="text-center py-2 align-middle">{{ $pelanggans->firstItem() + $index }}</td>
                    <td class="py-2 align-middle"><strong class="text-primary">{{ $item->kode_pelanggan ?? '-' }}</strong></td>
                    <td class="py-2 align-middle"><strong class="text-dark">{{ $item->nama ?? '-' }}</strong><br><small class="text-muted">ID: {{ $item->kode_pelanggan ?? '-' }}</small></td>
                    <td class="py-2 align-middle">{{ $item->no_hp ?? '-' }}</td>
                    <td class="py-2 align-middle">{{ optional($item->router)->nama ?? '-' }}</td>
                    <td class="py-2 align-middle"><span class="badge badge-light border px-2 py-1">{{ optional($item->paket)->nama_paket ?? '-' }}</span></td>
                    <td class="py-2 align-middle"><code>{{ $item->username_pppoe ?? '-' }}</code></td>
                    <td class="py-2 align-middle">
                        @if(in_array(strtolower(trim($item->status ?? '')), ['aktif','active']))
                            <span class="badge badge-success px-2 py-1">Aktif</span>
                        @else
                            <span class="badge badge-danger px-2 py-1">Non Aktif</span>
                        @endif
                    </td>
                    <td class="text-center py-2 align-middle">
                        <div class="btn-group btn-group-sm shadow-sm">
                            <a href="{{ route('pelanggan.show', $item->id) }}" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('pelanggan.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit text-white"></i></a>
                            <form action="{{ route('pelanggan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data pelanggan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4"><i class="fas fa-users-slash fa-2x text-muted mb-2"></i><br><span class="text-muted small">Belum ada data pelanggan.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-2 border-0">
        <div class="row align-items-center">
            <div class="col-md-6"><small class="text-muted" style="font-size:11px;">Total Pelanggan : <strong>{{ $totalPelanggan }}</strong></small></div>
            <div class="col-md-6 text-right">{{ $pelanggans->links() }}</div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card{border-radius:12px;}
.table td,.table th{vertical-align:middle;}
.badge{font-size:11px;font-weight:600;}
.btn{border-radius:6px;}
.main-header.navbar{background-color:#fff !important;border-bottom:1px solid #dee2e6 !important;}
.main-header.navbar .nav-link{color:#343a40 !important;}
.pagination{margin-bottom:0;}
</style>
@stop

@section('js')
<script>
$(function(){ $('[title]').tooltip(); });
</script>
@stop