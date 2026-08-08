@extends('adminlte::page')

@section('title', 'Edit Router')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-edit text-warning"></i>

            Edit Router MikroTik

        </h1>

        <small class="text-muted">

            Perbarui informasi Router MikroTik.

        </small>

    </div>

    <div>

        <a href="{{ route('router.index') }}"
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

        <i class="fas fa-ban"></i>

        Terjadi Kesalahan

    </h5>

    <ul class="mb-0">

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif

<form action="{{ route('router.update',$router->id) }}"
      method="POST">

    @csrf

    @method('PUT')

    <div class="row">

        <div class="col-lg-8">

            <div class="card card-warning card-outline shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi Router

                    </h3>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Nama Router</label>

                                <input
                                    type="text"
                                    name="nama_router"
                                    class="form-control"
                                    value="{{ old('nama_router',$router->nama_router) }}"
                                    required>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>IP Router</label>

                                <input
                                    type="text"
                                    name="ip_router"
                                    class="form-control"
                                    value="{{ old('ip_router',$router->ip_router) }}"
                                    required>

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-4">

                            <div class="form-group">

                                <label>API Port</label>

                                <input
                                    type="number"
                                    name="api_port"
                                    class="form-control"
                                    value="{{ old('api_port',$router->api_port) }}"
                                    required>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">

                                <label>Username</label>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    value="{{ old('username',$router->username) }}"
                                    required>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="form-group">

                                <label>Password</label>

                                <input
                                    type="text"
                                    name="password"
                                    class="form-control"
                                    value="{{ old('password',$router->password) }}"
                                    required>

                            </div>

                        </div>

                    </div>
                                        <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Lokasi</label>

                                <input
                                    type="text"
                                    name="lokasi"
                                    class="form-control"
                                    value="{{ old('lokasi', $router->lokasi) }}"
                                    placeholder="Contoh : Kantor Pusat">

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Versi RouterOS</label>

                                <input
                                    type="text"
                                    name="versi_routeros"
                                    class="form-control"
                                    value="{{ old('versi_routeros', $router->versi_routeros) }}"
                                    placeholder="Contoh : 7.19">

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>SSL</label>

                                <div class="custom-control custom-switch">

                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="ssl"
                                        name="ssl"
                                        value="1"
                                        {{ old('ssl', $router->ssl) ? 'checked' : '' }}>

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

                                <label>Status</label>

                                <select
                                    name="status"
                                    class="form-control">

                                    <option value="Aktif"
                                        {{ old('status', $router->status) == 'Aktif' ? 'selected' : '' }}>
                                        Aktif
                                    </option>

                                    <option value="Nonaktif"
                                        {{ old('status', $router->status) == 'Nonaktif' ? 'selected' : '' }}>
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
                        class="btn btn-warning">

                        <i class="fas fa-save"></i>

                        Update Router

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

                        Informasi Router

                    </h3>

                </div>

                <div class="card-body">

                    <table class="table table-sm">

                        <tr>

                            <th width="40%">ID</th>

                            <td>{{ $router->id }}</td>

                        </tr>

                        <tr>

                            <th>Dibuat</th>

                            <td>

                                {{ optional($router->created_at)->format('d M Y H:i') }}

                            </td>

                        </tr>

                        <tr>

                            <th>Diubah</th>

                            <td>

                                {{ optional($router->updated_at)->format('d M Y H:i') }}

                            </td>

                        </tr>

                    </table>

                    <hr>

                    <div class="alert alert-info mb-0">

                        <i class="fas fa-info-circle"></i>

                        Setelah mengubah data router, gunakan menu
                        <strong>Test Koneksi</strong>
                        untuk memastikan konfigurasi masih valid.

                    </div>

                </div>

            </div>

        </div>

    </div>

</form>

@stop