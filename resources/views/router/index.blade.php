@extends('adminlte::page')

@section('title', 'Router MikroTik')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 24px;">
            Router MikroTik
        </h1>
        <p class="text-muted text-sm mb-0">Kelola router, koneksi API, PPP Secret dan PPP Profile.</p>
    </div>
</div>
@stop

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm">
    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm">
    <i class="fas fa-times-circle mr-1"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<!-- Kotak Statistik Ringkasan -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-white shadow-sm border-left border-primary mb-3" style="border-left-width: 5px !important; border-radius: 8px;">
            <div class="inner p-3">
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">TOTAL ROUTER</p>
                <h3 class="font-weight-bold text-dark mb-0" style="font-size: 28px;">{{ $routers->count() }}</h3>
            </div>
            <div class="icon-stat"><i class="fas fa-server text-primary"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-white shadow-sm border-left border-success mb-3" style="border-left-width: 5px !important; border-radius: 8px;">
            <div class="inner p-3">
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">ROUTER AKTIF</p>
                <h3 class="font-weight-bold text-success mb-0" style="font-size: 28px;">{{ $routers->where('status','Aktif')->count() }}</h3>
            </div>
            <div class="icon-stat"><i class="fas fa-check-circle text-success"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-white shadow-sm border-left border-warning mb-3" style="border-left-width: 5px !important; border-radius: 8px;">
            <div class="inner p-3">
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">SSL ENABLED</p>
                <h3 class="font-weight-bold text-warning mb-0" style="font-size: 28px;">{{ $routers->where('ssl',true)->count() }}</h3>
            </div>
            <div class="icon-stat"><i class="fas fa-lock text-warning"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="small-box bg-white shadow-sm border-left border-info mb-3" style="border-left-width: 5px !important; border-radius: 8px;">
            <div class="inner p-3">
                <p class="text-muted text-uppercase font-weight-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">LOKASI ROUTER</p>
                <h3 class="font-weight-bold text-info mb-0" style="font-size: 28px;">{{ $routers->pluck('lokasi')->unique()->count() }}</h3>
            </div>
            <div class="icon-stat"><i class="fas fa-map-marker-alt text-info"></i></div>
        </div>
    </div>
</div>

<!-- Tabel Data Router -->
<div class="card card-outline card-primary shadow-sm" style="border-radius: 8px;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 16px;">
                <i class="fas fa-list mr-2 text-primary"></i> Daftar Router
            </h3>
        </div>
        <div>
            <a href="{{ route('router.create') }}" class="btn btn-primary btn-sm font-weight-bold shadow-sm px-3">
                <i class="fas fa-plus-circle mr-1"></i> Tambah Router
            </a>
            <a href="{{ route('router.index') }}" class="btn btn-light btn-sm font-weight-bold shadow-sm border ml-1 px-3">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped text-nowrap m-0">
            <thead>
                <tr class="bg-light text-secondary" style="font-size: 12px; text-transform: uppercase;">
                    <th width="50" class="py-3">#</th>
                    <th class="py-3">Nama Router</th>
                    <th class="py-3">IP Address</th>
                    <th class="py-3">API</th>
                    <th class="py-3">Lokasi</th>
                    <th class="py-3">Versi</th>
                    <th class="py-3">SSL</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Status API</th>
                    <th width="240" class="text-center py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routers as $router)
                <tr>
                    <td class="align-middle">{{ $loop->iteration }}</td>
                    <td class="align-middle font-weight-bold text-dark">{{ $router->nama_router }}</td>
                    <td class="align-middle"><span class="badge badge-light border px-2 py-1">{{ $router->ip_router }}</span></td>
                    <td class="align-middle">{{ $router->api_port }}</td>
                    <td class="align-middle">{{ $router->lokasi ?: '-' }}</td>
                    <td class="align-middle text-muted">{{ $router->versi_routeros ?: '-' }}</td>
                    <td class="align-middle">
                        @if($router->ssl)
                            <span class="badge badge-success px-2 py-1">SSL</span>
                        @else
                            <span class="badge badge-secondary px-2 py-1">Non SSL</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        @if($router->is_online)
                            <span class="badge badge-success px-2 py-1"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Online</span>
                        @else
                            <span class="badge badge-danger px-2 py-1"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Offline</span>
                        @endif
                        @if($router->last_checked_at)
                            <br><small class="text-muted" style="font-size: 11px;">{{ $router->last_checked_at->format('d/m/Y H:i') }}</small>
                        @endif
                    </td>
                    <td class="align-middle"><span class="text-muted">-</span></td>
                    <td class="align-middle text-center">
                        <div class="btn-group shadow-sm">
                            <a href="{{ route('router.edit', $router->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit text-white"></i></a>

                            {{-- Test Koneksi: gunakan route Laravel biasa karena RouterController@test mengembalikan redirect/session, bukan JSON --}}
                            <a href="{{ route('router.test', $router->id) }}" class="btn btn-success btn-sm" title="Test Koneksi">
                                <i class="fas fa-plug"></i>
                            </a>

                            <a href="{{ route('router.pppsecret', $router->id) }}" class="btn btn-primary btn-sm" title="PPP Secret"><i class="fas fa-user-lock"></i></a>
                            <a href="{{ route('router.pppprofile', $router->id) }}" class="btn btn-info btn-sm" title="PPP Profile"><i class="fas fa-id-card"></i></a>
                            <form action="{{ route('router.destroy', $router->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm rounded-right" onclick="return confirm('Hapus router ini?')" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="fas fa-server fa-3x mb-3 text-secondary opacity-50"></i>
                        <p class="mb-0">Belum ada data Router.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white py-3">
        <div class="row align-items-center">
            <div class="col-md-6"><span class="text-muted text-sm">Total Router : <strong class="text-dark">{{ $routers->count() }}</strong></span></div>
            <div class="col-md-6 text-right"><span class="text-muted text-sm">GNS Enterprise • Router Management</span></div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.main-header.navbar { background-color: #ffffff !important; border-bottom: 1px solid #dee2e6 !important; }
.main-header.navbar .nav-link { color: #343a40 !important; }
.small-box { border-radius: 8px; transition: all 0.3s ease-in-out; position: relative; overflow: hidden; }
.small-box:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important; }
.icon-stat { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 32px; opacity: 0.2; }
.table td { vertical-align: middle !important; }
.btn-group .btn { border-radius: 0; }
.btn-group .btn:first-child { border-top-left-radius: 4px; border-bottom-left-radius: 4px; }
.btn-group form:last-child .btn { border-top-right-radius: 4px; border-bottom-right-radius: 4px; }
</style>
@stop

@section('js')
<script>
$(function(){
    $('[title]').tooltip();
});
</script>
@stop
