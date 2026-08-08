@extends('adminlte::page')

@section('title', 'Edit Paket Internet')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">
        Edit Paket Internet
    </h1>
    <small class="text-muted" style="font-size: 11px;">
        Perbarui data paket internet dan hubungkan dengan PPP Profile MikroTik.
    </small>
</div>
@stop

@section('content')

@if ($errors->any())
<div class="alert alert-danger py-2 mb-2 small shadow-sm rounded-3" style="font-size: 11px;">
    <i class="fas fa-times-circle mr-1"></i> {{ $errors->first() }}
</div>
@endif

<form action="{{ route('paket.update', $paket->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-3">
        <div class="card-header bg-white py-3 border-0">
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 0.95rem; line-height: 1.5;">
                <i class="fas fa-wifi text-warning mr-2"></i> Formulir Edit Paket Internet
            </h3>
        </div>

        <div class="card-body px-4 py-3">
            <div class="row">
                {{-- KOLOM KIRI --}}
                <div class="col-md-6 pr-md-3">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Router *</label>
                        <select name="router_id" class="form-control form-control-sm" required>
                            <option value="">-- Pilih Router --</option>
                            @foreach($routers ?? [] as $router)
                                <option value="{{ $router->id }}" {{ old('router_id', $paket->router_id ?? '') == $router->id ? 'selected' : '' }}>
                                    {{ $router->nama_router }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Nama Paket *</label>
                        <input type="text" name="nama_paket" class="form-control form-control-sm" value="{{ old('nama_paket', $paket->nama_paket ?? '') }}" placeholder="Contoh: GNS HEMAT" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">PPP Profile MikroTik *</label>
                        <select name="ppp_profile" id="ppp_profile" class="form-control form-control-sm" required>
                            @php
                                $currentProfile = old('ppp_profile', $paket->ppp_profile ?? '');
                            @endphp
                            <option value="{{ $currentProfile }}" selected>{{ $currentProfile }}</option>
                        </select>
                    </div>
                </div>

                {{-- KOLOM KANAN --}}
                <div class="col-md-6 pl-md-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary">Kecepatan *</label>
                                <input type="text" name="kecepatan" class="form-control form-control-sm" value="{{ old('kecepatan', $paket->kecepatan ?? '') }}" placeholder="10 Mbps" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary">Harga (Rp) *</label>
                                <input type="number" name="harga" class="form-control form-control-sm" value="{{ old('harga', $paket->harga ?? '') }}" placeholder="100000" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Status *</label>
                        <select name="status" class="form-control form-control-sm" required>
                            <option value="Aktif" {{ old('status', $paket->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ old('status', $paket->status ?? '') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-secondary">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="form-control form-control-sm" placeholder="Keterangan opsional...">{{ old('keterangan', $paket->keterangan ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3 border-0 text-right">
            <a href="{{ route('paket.index') }}" class="btn btn-sm btn-light border px-4 font-weight-bold mr-2">Batal</a>
            <button type="submit" class="btn btn-sm btn-warning px-4 font-weight-bold shadow-sm text-dark">
                <i class="fas fa-save mr-1"></i> Update Paket Internet
            </button>
        </div>
    </div>
</form>

@stop

@section('css')
<style>
.card { 
    border-radius: 12px; 
}
.form-control-sm { 
    height: calc(1.5em + 0.5rem + 2px); 
    font-size: 12px; 
    border-radius: 6px; 
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
    let currentProfile = "{{ old('ppp_profile', $paket->ppp_profile ?? '') }}";
    let initialRouterId = $('[name="router_id"]').val();

    function fetchProfiles(routerId) {
        if (!routerId) return;
        
        $.get("{{ url('/router') }}/" + routerId + "/profiles", function(data) {
            let profiles = data.profiles || data;
            if (Array.isArray(profiles) && profiles.length > 0) {
                let options = '<option value="">-- Pilih PPP Profile --</option>';
                let found = false;

                profiles.forEach(function(profile) {
                    let isSelected = (profile === currentProfile) ? 'selected' : '';
                    if (isSelected) found = true;
                    options += `<option value="${profile}" ${isSelected}>${profile}</option>`;
                });

                if (currentProfile && !found) {
                    options += `<option value="${currentProfile}" selected>${currentProfile}</option>`;
                }

                $('#ppp_profile').html(options);
            }
        }).fail(function() {
            // JIKA AJAX GAGAL: Biarkan nilai currentProfile tetap aman di dropdown.
        });
    }

    if (initialRouterId) {
        fetchProfiles(initialRouterId);
    }

    $('[name="router_id"]').on('change', function() {
        let routerId = $(this).val();
        if (!routerId) return;
        
        $.get("{{ url('/router') }}/" + routerId + "/profiles", function(data) {
            let profiles = data.profiles || data;
            if (Array.isArray(profiles) && profiles.length > 0) {
                let options = '<option value="">-- Pilih PPP Profile --</option>';
                profiles.forEach(function(profile) {
                    options += `<option value="${profile}">${profile}</option>`;
                });
                $('#ppp_profile').html(options);
            }
        });
    });

    $('#ppp_profile').on('change', function() {
        let profile = $(this).val();
        if (profile) {
            let match = profile.match(/^C(\d+)/i);
            if (match) {
                $('[name="kecepatan"]').val(match[1] + ' Mbps');
            }
        }
    });

    $('form').on('submit', function(){
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupdate...');
    });
});
</script>
@stop