@extends('adminlte::page')

@section('title','Monitoring PPP Pelanggan')
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div><h1 class="m-0">Monitoring PPP Pelanggan</h1><small class="text-muted">Status pelanggan berdasarkan PPP Active MikroTik</small></div>
    <form method="POST" action="{{ route('mikrotik.ppp.monitor.sync') }}">@csrf
        <button class="btn btn-success btn-sm"><i class="fas fa-sync-alt mr-1"></i> Sinkron Sekarang</button>
    </form>
</div>
@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="row">
@foreach(['online'=>'ONLINE','offline'=>'OFFLINE','disabled'=>'DISABLED','router_offline'=>'ROUTER OFFLINE','unknown'=>'UNKNOWN'] as $key=>$label)
<div class="col-md-2 col-6"><div class="small-box bg-white border-left border-primary shadow-sm">
<div class="inner"><small>{{ $label }}</small><h3>{{ $summary[$key] }}</h3></div></div></div>
@endforeach
</div>

<div class="card card-outline card-primary">
<div class="card-body">
<form method="GET" class="row">
<div class="col-md-3 mb-2"><select name="router_id" class="form-control"><option value="">Semua Router</option>
@foreach($routers as $router)<option value="{{ $router->id }}" @selected($routerId==$router->id)>{{ $router->nama_router }}</option>@endforeach
</select></div>
<div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">Semua Status</option>
@foreach(['online'=>'Online','offline'=>'Offline','disabled'=>'Disabled','router_offline'=>'Router Offline','unknown'=>'Unknown'] as $v=>$l)
<option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>@endforeach
</select></div>
<div class="col-md-4 mb-2"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama, kode, username PPPoE"></div>
<div class="col-md-2 mb-2"><button class="btn btn-primary w-100">Filter</button></div>
</form>
</div>
<div class="table-responsive"><table class="table table-hover mb-0">
<thead><tr><th>NO</th><th>PELANGGAN</th><th>ROUTER</th><th>USERNAME</th><th>STATUS</th><th>IP</th><th>CALLER-ID</th><th>UPTIME</th><th>CHECK</th></tr></thead>
<tbody>
@forelse($pelanggans as $i=>$item)
@php $status=$item->ppp_status ?: 'unknown'; $badge=match($status){'online'=>'success','offline'=>'danger','disabled'=>'warning','router_offline'=>'dark',default=>'secondary'}; @endphp
<tr><td>{{ $pelanggans->firstItem()+$i }}</td><td><b>{{ $item->nama }}</b><br><small>{{ $item->kode_pelanggan }}</small></td>
<td>{{ optional($item->router)->nama_router ?: '-' }}</td><td><code>{{ $item->username_pppoe }}</code></td>
<td><span class="badge badge-{{ $badge }}">{{ strtoupper(str_replace('_',' ',$status)) }}</span></td>
<td>{{ $item->ppp_ip_address ?: '-' }}</td><td>{{ $item->ppp_caller_id ?: '-' }}</td><td>{{ $item->ppp_uptime ?: '-' }}</td>
<td>{{ optional($item->last_ppp_checked_at)->format('d/m/Y H:i:s') ?: '-' }}</td></tr>
@empty<tr><td colspan="9" class="text-center py-4">Belum ada data PPP pelanggan.</td></tr>@endforelse
</tbody></table></div>
<div class="card-footer">{{ $pelanggans->links() }}</div>
</div>
@stop
