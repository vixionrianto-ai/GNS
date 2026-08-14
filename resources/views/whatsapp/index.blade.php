@extends('adminlte::page')

@section('title', 'Riwayat WhatsApp')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h1 class="mb-1">
            <i class="fab fa-whatsapp text-success mr-2"></i>Riwayat WhatsApp
        </h1>
        <p class="text-muted mb-0">Monitoring seluruh pengiriman WhatsApp kepada pelanggan.</p>
    </div>
</div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('error') }}
    </div>
@endif

{{-- Statistik --}}
<div class="row">
    <div class="col-lg col-md-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($totalLog) }}</h3>
                <p>Total Log</p>
            </div>
            <div class="icon"><i class="fas fa-list"></i></div>
        </div>
    </div>

    <div class="col-lg col-md-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($totalSuccess) }}</h3>
                <p>Berhasil</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>

    <div class="col-lg col-md-4 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($totalFailed) }}</h3>
                <p>Gagal</p>
            </div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>

    <div class="col-lg col-md-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($totalPending) }}</h3>
                <p>Pending</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>

    <div class="col-lg col-md-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($totalHariIni) }}</h3>
                <p>Hari Ini</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-filter mr-1"></i> Filter Riwayat
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('whatsapp.index') }}">
            <div class="row align-items-end">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">Semua</option>
                        <option value="success" @selected(request('status') == 'success')>Berhasil</option>
                        <option value="failed" @selected(request('status') == 'failed')>Gagal</option>
                        <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Jenis</label>
                    <select name="jenis" class="form-control">
                        <option value="">Semua</option>
                        <option value="tagihan" @selected(request('jenis') == 'tagihan')>Tagihan</option>
                        <option value="h7" @selected(request('jenis') == 'h7')>H-7</option>
                        <option value="h3" @selected(request('jenis') == 'h3')>H-3</option>
                        <option value="isolir" @selected(request('jenis') == 'isolir')>Isolir</option>
                        <option value="pembayaran" @selected(request('jenis') == 'pembayaran')>Pembayaran</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control">
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label>Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama / Nomor / Invoice">
                </div>

                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-success mr-2 flex-fill">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                    <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary flex-fill">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card card-outline card-success shadow-sm">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-history mr-1"></i> Daftar Riwayat WhatsApp
        </h3>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" width="55">#</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Invoice</th>
                        <th>Nomor</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Provider</th>
                        <th class="text-center" width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-center">{{ $logs->firstItem() + $loop->index }}</td>
                        <td>
                            {{ $log->sent_at ? \Carbon\Carbon::parse($log->sent_at)->format('d-m-Y H:i') : '-' }}
                        </td>
                        <td>
                            @if($log->pelanggan)
                                <strong>{{ $log->pelanggan->nama }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $log->tagihan ? $log->tagihan->invoice_no : '-' }}</td>
                        <td>{{ $log->nomor }}</td>
                        <td>
                            @switch($log->jenis)
                                @case('tagihan') <span class="badge badge-primary">Tagihan</span> @break
                                @case('h7') <span class="badge badge-warning">H-7</span> @break
                                @case('h3') <span class="badge badge-info">H-3</span> @break
                                @case('isolir') <span class="badge badge-danger">Isolir</span> @break
                                @case('pembayaran') <span class="badge badge-success">Pembayaran</span> @break
                                @default <span class="badge badge-secondary">{{ $log->jenis }}</span>
                            @endswitch
                        </td>
                        <td>
                            @switch($log->status)
                                @case('success') <span class="badge badge-success">Berhasil</span> @break
                                @case('failed') <span class="badge badge-danger">Gagal</span> @break
                                @case('pending') <span class="badge badge-warning">Pending</span> @break
                                @default <span class="badge badge-secondary">{{ $log->status }}</span>
                            @endswitch
                        </td>
                        <td>{{ strtoupper($log->provider) }}</td>
                        <td class="text-center">
                            <a href="{{ route('whatsapp.show', $log) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="fab fa-whatsapp fa-3x mb-3"></i>
                            <br>Belum ada riwayat WhatsApp.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">{{ $logs->links() }}</div>
    </div>
</div>
@endsection

@push('css')
<style>
    .small-box { border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
    .small-box .icon { opacity: .18; }
    .card { border-radius: 8px; }
    .card-title { font-weight: 600; }
    .table th { white-space: nowrap; vertical-align: middle; }
    .table td { vertical-align: middle; white-space: nowrap; }
    .badge { font-size: 12px; padding: 6px 10px; }
    .btn, .form-control { border-radius: 6px; }
    .table-responsive { overflow-x: auto; }
</style>
@endpush

@push('js')
<script>
$(function () {
    setTimeout(function () {
        $('.alert').fadeOut(500);
    }, 4000);
});
</script>
@endpush
