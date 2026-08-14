@extends('adminlte::page')

@section('title', 'Tambah User')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1"><i class="fas fa-user-plus text-primary"></i> Tambah User</h1>
        <small class="text-muted">Tambahkan pengguna baru ke dalam sistem GNS Enterprise.</small>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow border-0">
            <div class="card-header bg-white"><h3 class="card-title">Informasi User</h3></div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Masukkan nama lengkap">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Masukkan alamat email">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <select id="user-role" name="role" class="form-control @error('role') is-invalid @enderror">
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" @selected(old('role') == $role->name)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div id="role-permissions" class="card border-primary mb-4" style="display:none;">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-key mr-1"></i> Hak Akses Role
                        </div>
                        <div class="card-body">
                            <div class="text-muted small mb-2">Permission mengikuti Role. Atur permission-nya melalui menu Role Management.</div>
                            <div id="permission-list" class="d-flex flex-wrap" style="gap:6px;"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow border-0">
            <div class="card-header bg-white"><h3 class="card-title">Informasi</h3></div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Petunjuk</h6>
                    <ul class="mb-0 pl-3">
                        <li>Nama harus diisi.</li>
                        <li>Email harus unik.</li>
                        <li>Password minimal mengikuti aturan Laravel.</li>
                        <li>Role menentukan hak akses user.</li>
                    </ul>
                </div>
                <table class="table table-sm">
                    <tr><th width="40%">Module</th><td>User Management</td></tr>
                    <tr><th>Sistem</th><td>GNS Enterprise</td></tr>
                    <tr><th>Status</th><td><span class="badge badge-success">Active</span></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roles = @json($roles->mapWithKeys(fn ($role) => [$role->name => $role->permissions->pluck('name')->values()->all()]));
    const select = document.getElementById('user-role');
    const box = document.getElementById('role-permissions');
    const list = document.getElementById('permission-list');

    function renderPermissions() {
        const permissions = roles[select.value] || [];
        list.innerHTML = '';

        if (!select.value) {
            box.style.display = 'none';
            return;
        }

        box.style.display = 'block';

        if (!permissions.length) {
            list.innerHTML = '<span class="text-muted">Role ini belum memiliki permission.</span>';
            return;
        }

        permissions.forEach(permission => {
            const badge = document.createElement('span');
            badge.className = 'badge badge-info';
            badge.textContent = permission;
            list.appendChild(badge);
        });
    }

    select.addEventListener('change', renderPermissions);
    renderPermissions();
});
</script>
@stop
