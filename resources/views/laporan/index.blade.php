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
            <a href="{{ route('laporan.index', request()->query()) }}"
               class="btn btn-sm btn-light border font-weight-bold bg-white">
                <i class="fas fa-sync mr-1"></i> Refresh
            </a>
            <a href="{{ route('laporan.export.pdf', request()->query()) }}"
               class="btn btn-sm btn-danger font-weight-bold ml-1" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <a href="{{ route('laporan.export.excel', request()->query()) }}"
               class="btn btn-sm btn-success font-weight-bold ml-1">
                <i class="fas fa-file-excel mr-1"></i> Excel
            </a>
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
.card {
    border-radius: 8px;
    transition: all 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.3rem 0.6rem rgba(0, 0, 0, 0.05) !important;
}

.btn {
    border-radius: 6px;
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
}

.laporan-filter-card .form-control,
.laporan-filter-card .input-group-text {
    border-radius: 6px;
}

.laporan-table thead th {
    background: #f8f9fa;
    color: #495057;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .02em;
    border-top: 0;
    border-bottom: 1px solid #e9ecef;
    padding: 11px 12px;
    white-space: nowrap;
}

.laporan-table tbody td {
    padding: 11px 12px;
    font-size: 12px;
    vertical-align: middle;
    border-color: #f0f2f4;
}

.laporan-table tbody tr:hover {
    background: #fafcff;
}

.laporan-table-card .card-header {
    padding: 14px 16px;
}

.laporan-table-card .pagination {
    margin-bottom: 0;
}

.main-header.navbar {
    background-color: #ffffff !important;
    border-bottom: 1px solid #dee2e6 !important;
}

.main-header.navbar .nav-link {
    color: #343a40 !important;
}

@media (max-width: 768px) {
    .laporan-table {
        min-width: 820px;
    }
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
