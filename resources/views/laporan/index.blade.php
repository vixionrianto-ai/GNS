@extends('adminlte::page')
@section('plugins.Chartjs', true)

@section('title', 'Laporan Keuangan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">
            Laporan Keuangan
        </h1>
        <small class="text-muted" style="font-size: 11px;">
            Dashboard laporan pendapatan dan tagihan GNS
        </small>
    </div>
    <div class="mt-2 mt-md-0">
        <div class="btn-group shadow-sm">
            <button class="btn btn-sm btn-light border font-weight-bold bg-white">
                <i class="fas fa-sync mr-1"></i>
                Refresh
            </button>
            <button class="btn btn-sm btn-danger font-weight-bold ml-1">
                <i class="fas fa-file-pdf mr-1"></i>
                PDF
            </button>
            <button class="btn btn-sm btn-success font-weight-bold ml-1">
                <i class="fas fa-file-excel mr-1"></i>
                Excel
            </button>
        </div>
    </div>
</div>
@stop

@section('content')

@include('laporan.partials.kpi')

@include('laporan.partials.filter')

<div class="row">
    @include('laporan.partials.grafik')

    @include('laporan.partials.statistik')
</div>

@include('laporan.partials.tabel')

@stop

@section('css')
<style>
/* Membuat kartu statistik ramping, minimalis, dan elegan */
.card { 
    border-radius: 8px; 
    transition: all 0.3s ease-in-out;
}

.card-body {
    padding: 0.5rem 0.75rem !important;
}

.card-body small, .card-body p, .card-body span {
    font-size: 10px !important;
}

.card-body h3, .card-body h4, .card-body h5 {
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    margin-bottom: 0 !important;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.3rem 0.6rem rgba(0, 0, 0, 0.05) !important;
}

.btn { 
    border-radius: 6px; 
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
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