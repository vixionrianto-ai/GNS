@extends('adminlte::page')

@section('title', 'Tambah Paket Internet')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size: 1.35rem;">
        Tambah Paket Internet
    </h1>
    <small class="text-muted" style="font-size: 11px;">
        Buat paket internet baru dan hubungkan dengan PPP Profile MikroTik.
    </small>
</div>
@stop

@section('content')

@if ($errors->any())
<div class="alert alert-danger py-2 mb-2 small shadow-sm rounded-3" style="font-size: 11px;">
    <i class="fas fa-times-circle mr-1"></i> {{ $errors->first() }}
</div>
@endif

<form action="{{ route('paket.store') }}" method="POST">
    @csrf

    <div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-3">
        <div class="card-header bg-white py-3 border-0">
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 0.95rem; line-height: 1.5;">
                <i class="fas fa-wifi text-primary mr-2"></i> Formulir Tambah Paket Internet
            </h3>
        </div>

        <div class="card-body px-4 py-3">
            <div class="row">
                {{-- KOLOM KIRI --}}
                <div class="col-md-6 pr-md-3">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Router *</label>
                        <select id="router_id" name="router_id" class="form-control form-control-sm" required>
                            <option value="">-- Pilih Router --</option>
                            @foreach($routers ?? [] as $router)
                                <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                                    {{ $router->nama_router }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Nama Paket *</label>
                        <input type="text" name="nama_paket" class="form-control form-control-sm" value="{{ old('nama_paket') }}" placeholder="Contoh: GNS HEMAT" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">PPP Profile MikroTik *</label>
                        <select name="profile_mikrotik" id="profile_mikrotik" class="form-control form-control-sm" required>
                            <option value="">-- Pilih Router terlebih dahulu --</option>
                        </select>
                    </div>
                </div>

                {{-- KOLOM KANAN --}}
                <div class="col-md-6 pl-md-3">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary">Kecepatan *</label>
                                <input type="text" name="kecepatan" class="form-control form-control-sm" value="{{ old('kecepatan') }}" placeholder="10 Mbps" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-secondary">Harga (Rp) *</label>
                                <input type="number" name="harga" class="form-control form-control-sm" value="{{ old('harga') }}" placeholder="100000" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Status *</label>
                        <select name="status" class="form-control form-control-sm" required>
                            <option value="Aktif" {{ old('status', 'Aktif') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ old('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-secondary">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="form-control form-control-sm" placeholder="Keterangan opsional...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white py-3 border-0 text-right">
            <a href="{{ route('paket.index') }}" class="btn btn-sm btn-light border px-4 font-weight-bold mr-2">Batal</a>
            <button type="submit" class="btn btn-sm btn-primary px-4 font-weight-bold shadow-sm">
                <i class="fas fa-save mr-1"></i> Simpan Paket Internet
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

    $('#router_id').change(function(){

        let router = $(this).val();
        let profile = $('#profile_mikrotik');

        profile.html('<option>Loading...</option>');

        if(router==''){
            profile.html('<option value="">-- Pilih Router terlebih dahulu --</option>');
            return;
        }

        $.get('/router/'+router+'/profiles', function(data){

            profile.empty();
            profile.append('<option value="">-- Pilih PPP Profile --</option>');

            $.each(data, function(i, item){
                profile.append('<option value="'+item+'">'+item+'</option>');
            });

        }).fail(function(){

            profile.html('<option value="">Router tidak dapat dihubungi</option>');

        });

    });

    $(document).on('change', '#profile_mikrotik', function(){
        let profile = $(this).val();
        if(profile){
            let match = profile.match(/^C(\d+)/i);
            if(match){
                $('[name="kecepatan"]').val(match[1] + ' Mbps');
            }
        }
    });

    $('form').on('submit', function(){
        let btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
    });

});
</script>
@stop