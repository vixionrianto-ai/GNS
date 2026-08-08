@extends('adminlte::page')

@section('title', 'Tambah Pelanggan Baru')

@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-1">
    <div>
        <h1 class="mb-0 font-weight-bold text-dark" style="font-size: 1.25rem;">
            <i class="fas fa-user-plus text-success mr-2"></i> Tambah Pelanggan Baru
        </h1>
        <small class="text-muted" style="font-size: 10px;">Tambahkan pelanggan baru dan sinkronkan dengan MikroTik.</small>
    </div>
    <ol class="breadcrumb float-sm-right mb-0 small" style="font-size: 11px;">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelanggan.index') }}">Pelanggan</a></li>
        <li class="breadcrumb-item active">Tambah</li>
    </ol>
</div>

@stop

@section('content')

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm py-1 mb-1 small" style="font-size: 11px;">
    <i class="fas fa-times-circle mr-1"></i> {{ $errors->first() }}
    <button type="button" class="close py-0" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<form action="{{ route('pelanggan.store') }}" method="POST" autocomplete="off">
    @csrf

    <div class="card card-success card-outline shadow-sm rounded-4 border-0 mb-1">
        <div class="card-header bg-white py-2 border-0">
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 0.95rem; line-height: 1.8;">
                <i class="fas fa-id-card text-success mr-2"></i> Formulir Tambah Data Pelanggan
            </h3>
            <div class="card-tools">
                <a href="{{ route('pelanggan.index') }}" class="btn btn-sm btn-secondary px-3 shadow-sm font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body px-3 py-1">
            <div class="row">
                
                {{-- KOLOM KIRI: Informasi Pelanggan & Layanan Internet --}}
                <div class="col-lg-7 pr-lg-2">
                    
                    {{-- 1. Informasi Pelanggan --}}
                    <div class="p-2 mb-2 bg-light rounded-3 border">
                        <h6 class="text-primary font-weight-bold mb-1" style="font-size: 11px;">
                            <i class="fas fa-user mr-1"></i> Informasi Pelanggan
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Nama Pelanggan *</label>
                                    <input type="text" name="nama" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('nama') }}" placeholder="Nama lengkap..." required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Nomor HP / WA *</label>
                                    <input type="text" name="no_hp" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('no_hp') }}" placeholder="08123456789" required autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Alamat Lengkap *</label>
                            <textarea name="alamat" rows="1" class="form-control form-control-sm" style="font-size: 12px; min-height: 32px;" placeholder="Alamat pelanggan..." required autocomplete="off">{{ old('alamat') }}</textarea>
                        </div>
                    </div>

                    {{-- 2. Layanan Internet & PPPoE --}}
                    <div class="p-2 mb-1 bg-light rounded-3 border">
                        <h6 class="text-primary font-weight-bold mb-1" style="font-size: 11px;">
                            <i class="fas fa-wifi mr-1"></i> Layanan Internet & PPPoE
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Paket Internet *</label>
                                    <select name="paket_id" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" required>
                                        <option value="">-- Pilih Paket --</option>
                                        @foreach($pakets as $paket)
                                            <option value="{{ $paket->id }}" {{ old('paket_id') == $paket->id ? 'selected' : '' }}>
                                                {{ $paket->nama_paket }} (Rp {{ number_format($paket->harga,0,',','.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Router MikroTik *</label>
                                    <select name="router_id" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" required>
                                        <option value="">-- Pilih Router --</option>
                                        @foreach($routers as $router)
                                            <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                                {{ $router->nama_router }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Username PPPoE *</label>
                                    <input type="text" name="username_pppoe" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('username_pppoe') }}" placeholder="Username" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Password PPPoE *</label>
                                    <input type="text" name="password_pppoe" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('password_pppoe') }}" placeholder="Password" required autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN: Jaringan, Aktivasi, & Auto Isolir --}}
                <div class="col-lg-5 pl-lg-2">
                    
                    {{-- 3. Jaringan & Aktivasi --}}
                    <div class="p-2 mb-2 bg-light rounded-3 border">
                        <h6 class="text-primary font-weight-bold mb-1" style="font-size: 11px;">
                            <i class="fas fa-network-wired mr-1"></i> Jaringan & Aktivasi
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">IP Address (Opsional)</label>
                                    <input type="text" name="ip_address" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('ip_address') }}" placeholder="DHCP" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">MAC Address (Opsional)</label>
                                    <input type="text" name="mac_address" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('mac_address') }}" placeholder="AA:BB:CC..." autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Tgl Pasang</label>
                                    <input type="date" name="tanggal_pasang" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('tanggal_pasang', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Tgl Aktif</label>
                                    <input type="date" name="tanggal_aktif" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('tanggal_aktif', date('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Status</label>
                            <select name="status" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;">
                                <option value="Aktif" {{ old('status','Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                        </div>
                    </div>

                    {{-- 4. Pengaturan Auto Isolir --}}
                    <div class="p-2 bg-light rounded-3 border">
                        <h6 class="text-primary font-weight-bold mb-1" style="font-size: 11px;">
                            <i class="fas fa-user-lock mr-1"></i> Pengaturan Auto Isolir
                        </h6>
                        <div class="form-group mb-1">
                            <div class="custom-control custom-radio custom-control-inline mr-3">
                                <input type="radio" id="isolation_default" name="isolation_use_default" value="1" class="custom-control-input" checked>
                                <label class="custom-control-label font-weight-normal small" for="isolation_default" style="font-size: 11px;">Default (2 periode)</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="isolation_custom" name="isolation_use_default" value="0" class="custom-control-input">
                                <label class="custom-control-label font-weight-normal small" for="isolation_custom" style="font-size: 11px;">Khusus</label>
                            </div>
                        </div>
                        <div class="form-group mb-0" id="customIsolationArea" style="display:none;">
                            <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Isolir Setelah Tunggakan</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="2" name="isolation_period_limit" class="form-control form-control-sm" style="font-size: 12px; height: calc(1.5em + .5rem + 2px);" value="2">
                                <div class="input-group-append">
                                    <span class="input-group-text py-0" style="font-size: 11px;">Periode</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="card-footer bg-white py-2 border-0 text-right">
            <button type="submit" class="btn btn-sm btn-success px-4 font-weight-bold shadow-sm">
                <i class="fas fa-save mr-1"></i> Simpan Pelanggan & Generate Tagihan
            </button>
        </div>
    </div>
</form>

@stop

@section('css')
<style>
.card { border-radius: 12px; }
.form-control { border-radius: 6px; transition: all .2s ease-in-out; }
.form-control:focus { box-shadow: 0 0 0 .2rem rgba(40,167,69,.20); border-color: #28a745; }
.btn { border-radius: 6px; }
label { font-weight: 600; color: #495057; }
</style>
@stop

@section('js')
<script>
$(function(){
    $('form').on('submit', function(){
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
    });

    const radioDefault = document.getElementById('isolation_default');
    const radioCustom = document.getElementById('isolation_custom');
    const customArea = document.getElementById('customIsolationArea');

    function toggleIsolationArea() {
        if (radioCustom && radioCustom.checked) {
            $(customArea).slideDown();
        } else {
            $(customArea).slideUp();
        }
    }

    if(radioDefault && radioCustom) {
        $(radioDefault).on('change', toggleIsolationArea);
        $(radioCustom).on('change', toggleIsolationArea);
        toggleIsolationArea();
    }
});
</script>
@stop