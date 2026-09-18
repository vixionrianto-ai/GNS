@extends('adminlte::page')

@section('title','PPP Active')
@section('content_header')
<div class="d-flex justify-content-between align-items-center"><div><h1>PPP Active</h1><small>{{ $router->nama_router }}</small></div>
<a href="{{ route('mikrotik.ppp.monitor',['router_id'=>$router->id]) }}" class="btn btn-primary btn-sm">Monitoring Pelanggan</a></div>
@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card card-outline card-success"><div class="card-header"><b>{{ count($sessions) }} Session Aktif</b></div>
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>USERNAME</th><th>ADDRESS</th><th>CALLER-ID</th><th>UPTIME</th><th>SERVICE</th><th>AKSI</th></tr></thead><tbody>
@forelse($sessions as $session)<tr><td><code>{{ $session['name'] ?? '-' }}</code></td><td>{{ $session['address'] ?? '-' }}</td><td>{{ $session['caller-id'] ?? '-' }}</td><td>{{ $session['uptime'] ?? '-' }}</td><td>{{ $session['service'] ?? '-' }}</td><td>
@if(!empty($session['name']))<form method="POST" action="{{ route('router.pppactive.disconnect',[$router->id,urlencode($session['name'])]) }}" onsubmit="return confirm('Putus session PPP ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Disconnect</button></form>@endif
</td></tr>@empty<tr><td colspan="6" class="text-center py-4">Tidak ada PPP Active.</td></tr>@endforelse
</tbody></table></div></div>
@stop
