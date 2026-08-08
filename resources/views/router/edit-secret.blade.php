@extends('adminlte::page')

@section('title', 'Edit PPP Secret')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-user-edit text-warning"></i>

            Edit PPP Secret

        </h1>

        <small class="text-muted">

            Router :

            <strong>

                {{ $router->nama_router }}

            </strong>

        </small>

    </div>

    <div>

        <a
            href="{{ route('router.pppsecret',$router->id) }}"
            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

    </div>

</div>

@stop


@section('content')

@if($errors->any())

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

<form
    action="{{ route('router.pppsecret.update', [$router->id, $secret['name']]) }}"
    method="POST">

    @csrf

    @method('PUT')

    <div class="row">

        <div class="col-lg-8">

            <div class="card card-warning card-outline shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi PPP Secret

                    </h3>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Username</label>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    value="{{ old('username',$secret['name']) }}"
                                    required>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Password</label>

                                <input
                                    type="text"
                                    name="password"
                                    class="form-control"
                                    value="{{ old('password',$secret['password'] ?? '') }}"
                                    required>

                            </div>

                        </div>

                    </div>

                    <div class="row">
                                                <div class="col-md-6">

                            <div class="form-group">

                                <label>Service</label>

                                <select
                                    name="service"
                                    class="form-control">

                                    <option value="pppoe"
                                        {{ ($secret['service'] ?? '') == 'pppoe' ? 'selected' : '' }}>

                                        PPPoE

                                    </option>

                                    <option value="any"
                                        {{ ($secret['service'] ?? '') == 'any' ? 'selected' : '' }}>

                                        Any

                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Profile</label>

                                <select
                                    name="profile"
                                    class="form-control">

                                    @foreach($profiles as $profile)

                                        <option
                                            value="{{ $profile['name'] }}"
                                            {{ ($secret['profile'] ?? '') == $profile['name'] ? 'selected' : '' }}>

                                            {{ $profile['name'] }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>

                    </div>

                    <div class="form-group">

                        <label>Status</label>

                        <select
                            name="disabled"
                            class="form-control">

                            <option value="false"
                                {{ ($secret['disabled'] ?? 'false') == 'false' ? 'selected' : '' }}>

                                Aktif

                            </option>

                            <option value="true"
                                {{ ($secret['disabled'] ?? 'false') == 'true' ? 'selected' : '' }}>

                                Disabled

                            </option>

                        </select>

                    </div>

                </div>

                <div class="card-footer">

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="fas fa-save"></i>

                        Update PPP Secret

                    </button>

                    <a
                        href="{{ route('router.pppsecret',$router->id) }}"
                        class="btn btn-secondary">

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

                        Perubahan akan langsung disinkronkan ke Router MikroTik.

                    </p>

                    <hr>

                    <p>

                        Pastikan profile yang dipilih masih tersedia pada router.

                    </p>

                    <hr>

                    <p class="text-muted mb-0">

                        Gunakan status <strong>Disabled</strong> jika ingin menonaktifkan akun tanpa menghapusnya.

                    </p>

                </div>

            </div>

        </div>

    </div>

</form>

@stop