@extends('adminlte::page')

@section('title', 'Paket Internet')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">
        Paket Internet
    </h1>
    <small class="text-muted" style="font-size: 11px;">
        Kelola seluruh paket internet GNS Enterprise.
    </small>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3" style="font-size: 11px;">
    <i class="fas fa-check-circle mr-1"></i>
    {{ session('success') }}
    <button class="close py-0" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 mb-2 small rounded-3" style="font-size: 11px;">
    <i class="fas fa-times-circle mr-1"></i>
    {{ session('error') }}
    <button class="close py-0" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>
@endif

<!-- Kotak Statistik Ringkasan -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #007bff !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">TOTAL PAKET</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 1.3rem;">
                        {{ $pakets->count() }}
                    </h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(0,123,255,0.1);">
                    <i class="fas fa-box text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #28a745 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">PAKET AKTIF</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 1.3rem;">
                        {{ $pakets->where('status','Aktif')->count() }}
                    </h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(40,167,69,0.1);">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">NONAKTIF</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 1.3rem;">
                        {{ $pakets->where('status','!=','Aktif')->count() }}
                    </h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(220,53,69,0.1);">
                    <i class="fas fa-times-circle text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="card shadow-sm border-0 rounded-4 mb-2" style="border-left: 4px solid #17a2b8 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                    <span class="text-muted small font-weight-bold text-uppercase" style="font-size: 9px;">PAKET TERMAHAL</span>
                    <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 1.15rem;">
                        Rp {{ number_format($pakets->max('harga') ?? 0,0,',','.') }}
                    </h3>
                </div>
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: rgba(23,162,184,0.1);">
                    <i class="fas fa-money-bill-wave text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Data Paket Internet -->
<div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-2">
    <div class="card-header bg-white py-2 border-0">
        <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 0.95rem; line-height: 1.8;">
            <i class="fas fa-list text-primary mr-2"></i>
            Data Paket Internet
        </h3>
        <div class="card-tools">
            <a href="{{ route('paket.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm font-weight-bold">
                <i class="fas fa-plus-circle mr-1"></i>
                Tambah Paket
            </a>
            <a href="{{ route('paket.index') }}" class="btn btn-sm btn-light border px-3 shadow-sm font-weight-bold ml-1">
                <i class="fas fa-sync-alt mr-1"></i>
                Refresh
            </a>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0 text-sm">
            <thead class="bg-light">
                <tr>
                    <th width="60" class="text-center py-2">NO</th>
                    <th class="py-2">NAMA PAKET</th>
                    <th class="py-2">KECEPATAN</th>
                    <th class="py-2">HARGA</th>
                    <th class="py-2">STATUS</th>
                    <th width="140" class="text-center py-2">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pakets as $paket)
                <tr>
                    <td class="text-center py-2 align-middle">
                        {{ $loop->iteration }}
                    </td>
                    <td class="py-2 align-middle">
                        <strong class="text-dark">
                            {{ $paket->nama_paket }}
                        </strong>
                    </td>
                    <td class="py-2 align-middle">
                        <span class="badge badge-info px-2 py-1">
                            {{ $paket->kecepatan }}
                        </span>
                    </td>
                    <td class="py-2 align-middle">
                        <strong class="text-success">
                            Rp {{ number_format($paket->harga,0,',','.') }}
                        </strong>
                    </td>
                    <td class="py-2 align-middle">
                        @if($paket->status === \App\Models\Paket::STATUS_AKTIF)
                            <span class="badge badge-success px-2 py-1">
                                Aktif
                            </span>
                        @else
                            <span class="badge badge-danger px-2 py-1">
                                Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="text-center py-2 align-middle">
                        <div class="btn-group btn-group-sm shadow-sm">
                            <a href="{{ route('paket.edit',$paket->id) }}" class="btn btn-warning btn-sm shadow-sm" title="Edit">
                                <i class="fas fa-edit text-white"></i>
                            </a>
                            <form action="{{ route('paket.destroy',$paket->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus paket {{ $paket->nama_paket }} ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm shadow-sm ml-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-wifi fa-2x text-muted mb-2"></i>
                        <br>
                        <span class="text-muted small">
                            Belum ada Paket Internet.
                        </span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white py-2 border-0">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted" style="font-size: 11px;">
                    Total Paket : 
                    <strong>
                        {{ $pakets->count() }}
                    </strong>
                </small>
            </div>
            <div class="col-md-6 text-right">
                <small class="text-muted" style="font-size: 11px;">
                    GNS Enterprise • Paket Management
                </small>
            </div>
        </div>
    </div>
</div>

@stop


@section('css')
<style>
.card {
    border-radius: 12px;
}
.table td, .table th {
    vertical-align: middle;
}
.badge {
    font-size: 11px;
    font-weight: 600;
}
.btn {
    border-radius: 6px;
}
/* Menjadikan navbar atas putih bersih seragam */
.main-header.navbar {
    background-color: #ffffff !important;
    border-bottom: 1px solid #dee2e6 !important;
}
.main-header.navbar .nav-link {
    color: #343a40 !important;
}
</style>
@stop


@section('js')
<script>
$(function(){
    $('[title]').tooltip();
});
</script>
@stop