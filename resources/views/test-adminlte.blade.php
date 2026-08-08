@extends('adminlte::page')

@section('title', 'Test AdminLTE')

@section('content_header')
    <h1>GNS AdminLTE Test</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">AdminLTE Berhasil</h3>
        </div>

        <div class="card-body">
            <h4>Selamat 🎉</h4>

            <p>AdminLTE sudah berjalan dengan baik.</p>

            <a href="{{ route('dashboard') }}" class="btn btn-success">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
@stop