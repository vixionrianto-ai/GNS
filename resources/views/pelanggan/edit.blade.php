@extends('adminlte::page')

@section('title', 'Edit Pelanggan')

@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap mb-1">
    <div>
        <h1 class="mb-0 font-weight-bold text-dark" style="font-size: 1.25rem;">
            <i class="fas fa-user-edit text-warning mr-2"></i> Edit Pelanggan
        </h1>
        <small class="text-muted" style="font-size: 10px;">Perbarui data pelanggan GNS Network dan sinkronkan konfigurasi MikroTik.</small>
    </div>
    <ol class="breadcrumb float-sm-right mb-0 small" style="font-size: 11px;">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pelanggan.index') }}">Pelanggan</a></li>
        <li class="breadcrumb-item active">Edit</li>
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

<form action="{{ route('pelanggan.update', $pelanggan->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card card-warning card-outline shadow-sm rounded-4 border-0 mb-1">
        <div class="card-header bg-white py-2 border-0">
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 0.95rem; line-height: 1.8;">
                <i class="fas fa-id-card text-warning mr-2"></i> Formulir Edit Data Pelanggan
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
                                    <input type="text" name="nama" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('nama', $pelanggan->nama) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Nomor HP / WA *</label>
                                    <input type="text" name="no_hp" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('no_hp', $pelanggan->no_hp) }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Alamat Lengkap *</label>
                            <textarea name="alamat" rows="1" class="form-control form-control-sm" style="font-size: 12px; min-height: 32px;" required>{{ old('alamat', $pelanggan->alamat) }}</textarea>
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
                                            <option value="{{ $paket->id }}" {{ old('paket_id', $pelanggan->paket_id) == $paket->id ? 'selected' : '' }}>
                                                {{ $paket->nama_paket }} ({{ $paket->kecepatan }}) - Rp {{ number_format($paket->harga,0,',','.') }}
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
                                            <option value="{{ $router->id }}" {{ old('router_id', $pelanggan->router_id) == $router->id ? 'selected' : '' }}>
                                                {{ $router->nama_router }} ({{ $router->ip_router }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Username PPPoE</label>
                                    <input type="text" name="username_pppoe" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('username_pppoe', $pelanggan->username_pppoe) }}" placeholder="Username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Password PPPoE</label>
                                    <input type="text" name="password_pppoe" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('password_pppoe', $pelanggan->password_pppoe) }}" placeholder="Password">
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
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">IP Address</label>
                                    <input type="text" name="ip_address" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('ip_address', $pelanggan->ip_address) }}" placeholder="DHCP">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">MAC Address</label>
                                    <input type="text" name="mac_address" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('mac_address', $pelanggan->mac_address) }}" placeholder="AA:BB:CC...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Tgl Pasang</label>
                                    <input type="date" name="tanggal_pasang" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('tanggal_pasang', optional($pelanggan->tanggal_pasang)->format('Y-m-d') ?? $pelanggan->tanggal_pasang) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Tgl Aktif</label>
                                    <input type="date" name="tanggal_aktif" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;" value="{{ old('tanggal_aktif', optional($pelanggan->tanggal_aktif)->format('Y-m-d') ?? $pelanggan->tanggal_aktif) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Status</label>
                            <select name="status" class="form-control form-control-sm py-1" style="height: calc(1.5em + .5rem + 2px); font-size: 12px;">
                                <option value="Aktif" {{ old('status', $pelanggan->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Nonaktif" {{ old('status', $pelanggan->status) == 'Nonaktif' ? 'selected' : '' }}>Non Aktif</option>
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
                                <input type="radio" id="isolation_default" name="isolation_use_default" value="1" class="custom-control-input" {{ old('isolation_use_default', $pelanggan->isolation_use_default) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-normal small" for="isolation_default" style="font-size: 11px;">Default (2 periode)</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="isolation_custom" name="isolation_use_default" value="0" class="custom-control-input" {{ !old('isolation_use_default', $pelanggan->isolation_use_default) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-normal small" for="isolation_custom" style="font-size: 11px;">Khusus</label>
                            </div>
                        </div>
                        <div class="form-group mb-0" id="customIsolationArea">
                            <label class="small font-weight-bold text-secondary mb-0" style="font-size: 10px;">Isolir Setelah Tunggakan</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="2" name="isolation_period_limit" class="form-control form-control-sm" style="font-size: 12px; height: calc(1.5em + .5rem + 2px);" value="{{ old('isolation_period_limit', $pelanggan->isolation_period_limit ?? 2) }}">
                                <div class="input-group-append">
                                    <span class="input-group-text py-0" style="font-size: 11px;">Periode</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>


        <div class="card card-outline card-info shadow-sm rounded-4 border-0 mb-2">
            <div class="card-header bg-white py-2">
                <h3 class="card-title font-weight-bold"><i class="fas fa-map-marker-alt text-info mr-1"></i> Lokasi Pelanggan (Opsional)</h3>
            </div>
            <div class="card-body">
                <small class="text-muted d-block mb-2">Klik titik pada peta untuk menyimpan koordinat pelanggan. Geser penanda untuk menyesuaikan posisi.</small>
                <div id="customer-location-map" style="height:300px;border-radius:8px;z-index:1"></div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label for="latitude">Latitude</label>
                        <input id="latitude" name="latitude" type="number" step="any" min="-90" max="90" class="form-control form-control-sm" value="{{ old('latitude', $pelanggan->latitude) }}" placeholder="-7.545123">
                    </div>
                    <div class="col-md-6">
                        <label for="longitude">Longitude</label>
                        <input id="longitude" name="longitude" type="number" step="any" min="-180" max="180" class="form-control form-control-sm" value="{{ old('longitude', $pelanggan->longitude) }}" placeholder="112.234567">
                    </div>
                </div>
                <a id="open-customer-map" class="btn btn-sm btn-outline-primary mt-2" href="https://www.google.com/maps" target="_blank" rel="noopener">
                    <i class="fas fa-map-marked-alt mr-1"></i> Buka Google Maps
                </a>
            </div>
        </div>

        <div class="card-footer bg-white py-2 border-0 text-right">
            <button type="submit" class="btn btn-sm btn-warning px-4 font-weight-bold text-dark shadow-sm">
                <i class="fas fa-save mr-1"></i> Update Pelanggan
            </button>
        </div>
    </div>
</form>

@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
.card { border-radius: 12px; }
.form-control { border-radius: 6px; transition: all .2s ease-in-out; }
.form-control:focus { box-shadow: 0 0 0 .2rem rgba(255,193,7,.20); border-color: #ffc107; }
.btn { border-radius: 6px; }
label { font-weight: 600; color: #495057; }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
$(function () {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const mapElement = document.getElementById('customer-location-map');
    const googleLink = document.getElementById('open-customer-map');
    if (!latInput || !lngInput || !mapElement || !window.L) return;
    const defaultCenter = [-7.545123, 112.234567];
    const hasCoordinates = latInput.value !== '' && lngInput.value !== '';
    const initialCenter = hasCoordinates ? [parseFloat(latInput.value), parseFloat(lngInput.value)] : defaultCenter;
    const map = L.map(mapElement).setView(initialCenter, hasCoordinates ? 17 : 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    let marker = null;
    function syncCoordinates(lat, lng) {
        latInput.value = Number(lat).toFixed(7);
        lngInput.value = Number(lng).toFixed(7);
        googleLink.href = 'https://www.google.com/maps?q=' + latInput.value + ',' + lngInput.value;
    }
    function placeMarker(lat, lng) {
        if (marker) marker.setLatLng([lat, lng]);
        else marker = L.marker([lat, lng], {draggable: true}).addTo(map);
        marker.off('dragend').on('dragend', function (event) {
            const point = event.target.getLatLng();
            syncCoordinates(point.lat, point.lng);
        });
        syncCoordinates(lat, lng);
    }
    if (hasCoordinates) placeMarker(initialCenter[0], initialCenter[1]);
    map.on('click', event => placeMarker(event.latlng.lat, event.latlng.lng));
    function updateFromInputs() {
        const lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            placeMarker(lat, lng); map.panTo([lat, lng]);
        } else if (latInput.value === '' || lngInput.value === '') googleLink.href = 'https://www.google.com/maps';
    }
    latInput.addEventListener('change', updateFromInputs);
    lngInput.addEventListener('change', updateFromInputs);
    if (hasCoordinates) syncCoordinates(initialCenter[0], initialCenter[1]);
});
</script>
<script>
$(function(){
    $('form').on('submit', function(){
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupdate...');
    });

    $('input[name="isolation_use_default"]').on('change', function () {
        if ($('#isolation_custom').is(':checked')) {
            $('#customIsolationArea').slideDown();
        } else {
            $('#customIsolationArea').slideUp();
        }
    });

    $('input[name="isolation_use_default"]:checked').trigger('change');
});
</script>
@stop