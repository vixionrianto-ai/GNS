@extends('adminlte::page')

@section('title', 'Tambah Router')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-server text-primary"></i>

            Tambah Router

        </h1>

        <small class="text-muted">

            Tambahkan Router MikroTik baru ke sistem GNS.

        </small>

    </div>

    <div>

        <a

            href="{{ route('router.index') }}"

            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

    </div>

</div>

@stop


@section('content')
@if ($errors->any())

<div class="alert alert-danger">

    <h5>

        <i class="icon fas fa-ban"></i>

        Terjadi Kesalahan

    </h5>

    <ul class="mb-0">

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif
<form

    action="{{ route('router.store') }}"

    method="POST">

    @csrf

    <div class="row">

        <div class="col-lg-8">

            <div class="card card-primary card-outline shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi Router

                    </h3>

                </div>

                <div class="card-body">
                    <div class="row">

    <div class="col-md-6">

        <div class="form-group">

            <label>

                Nama Router

            </label>

            <input

                type="text"

                name="nama_router"

                class="form-control @error('nama_router') is-invalid @enderror"

                value="{{ old('nama_router') }}"

                required>

            @error('nama_router')

                <div class="invalid-feedback">

                    {{ $message }}

                </div>

            @enderror

        </div>

    </div>

    <div class="col-md-6">

        <div class="form-group">

            <label>

                IP Router

            </label>

            <input

                type="text"

                name="ip_router"

                class="form-control @error('ip_router') is-invalid @enderror"

                value="{{ old('ip_router') }}"

                placeholder="192.168.88.1"

                required>

        </div>

    </div>

</div>

<div class="row">

    <div class="col-md-4">

        <div class="form-group">

            <label>

                API Port

            </label>

            <input

                type="number"

                name="api_port"

                class="form-control"

                value="{{ old('api_port',8728) }}">

        </div>

    </div>

    <div class="col-md-4">

        <div class="form-group">

            <label>

                Username

            </label>

            <input

                type="text"

                name="username"

                class="form-control"

                value="{{ old('username') }}">

        </div>

    </div>

    <div class="col-md-4">

        <div class="form-group">

            <label>

                Password

            </label>

            <input

                type="password"

                name="password"

                class="form-control">

        </div>

    </div>

</div>
<div class="row">

    <div class="col-md-6">

        <div class="form-group">

            <label>

                Lokasi

            </label>

            <input

                type="text"

                name="lokasi"

                class="form-control"

                value="{{ old('lokasi') }}"

                placeholder="Contoh : Kantor Pusat">

        </div>

    </div>

    <div class="col-md-6">

        <div class="form-group">

            <label>

                Versi RouterOS

            </label>

            <input

                type="text"

                name="versi_routeros"

                class="form-control"

                value="{{ old('versi_routeros') }}"

                placeholder="Contoh : 7.19">

        </div>

    </div>

</div>

<div class="row">

    <div class="col-md-6">

        <div class="form-group">

            <label>

                SSL

            </label>

            <div class="custom-control custom-switch">

                <input

                    type="checkbox"

                    class="custom-control-input"

                    id="ssl"

                    name="ssl"

                    value="1"

                    {{ old('ssl') ? 'checked' : '' }}>

                <label

                    class="custom-control-label"

                    for="ssl">

                    Gunakan SSL

                </label>

            </div>

        </div>

    </div>

    <div class="col-md-6">

        <div class="form-group">

            <label>

                Status

            </label>

            <select

                name="status"

                class="form-control">

                <option value="Aktif"

                    {{ old('status')=='Aktif' ? 'selected' : '' }}>

                    Aktif

                </option>

                <option value="Nonaktif"

                    {{ old('status')=='Nonaktif' ? 'selected' : '' }}>

                    Nonaktif

                </option>

            </select>

        </div>

    </div>

</div>
                </div>

                <div class="card-footer">

                    <button

                        type="submit"

                        class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Simpan Router

                    </button>

                    <a

                        href="{{ route('router.index') }}"

                        class="btn btn-secondary">

                        <i class="fas fa-times"></i>

                        Batal

                    </a>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card card-info card-outline shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi

                    </h3>

                </div>

                <div class="card-body">

                    <p>

                        <strong>API Port Default</strong>

                    </p>

                    <p>

                        8728 (Non SSL)

                    </p>

                    <hr>

                    <p>

                        <strong>API SSL</strong>

                    </p>

                    <p>

                        8729 (SSL)

                    </p>

                    <hr>

                    <p class="text-muted">

                        Pastikan service API pada MikroTik sudah aktif sebelum melakukan koneksi.

                    </p>

                </div>

            </div>

        </div>

    </div>

</form>

@stop