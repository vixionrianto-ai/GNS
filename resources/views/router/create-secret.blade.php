@extends('adminlte::page')

@section('title', 'Tambah PPP Secret')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-user-plus text-primary"></i>

            Tambah PPP Secret

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

    action="{{ route('router.pppsecret.store',$router->id) }}"

    method="POST">

    @csrf

    <div class="row">

        <div class="col-lg-8">

            <div class="card card-primary card-outline shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi PPP Secret

                    </h3>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>

                                    Username

                                </label>

                                <input

                                    type="text"

                                    name="username"

                                    class="form-control"

                                    value="{{ old('username') }}"

                                    required>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>

                                    Password

                                </label>

                                <input

                                    type="text"

                                    name="password"

                                    class="form-control"

                                    value="{{ old('password') }}"

                                    required>

                            </div>

                        </div>

                    </div>

                    <div class="row">
                                                <div class="col-md-6">

                            <div class="form-group">

                                <label>

                                    Service

                                </label>

                                <select

                                    name="service"

                                    class="form-control">

                                    <option value="pppoe">

                                        PPPoE

                                    </option>

                                    <option value="any">

                                        Any

                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>

                                    Profile

                                </label>

                                <select

                                    name="profile"

                                    class="form-control">

                                    @foreach($profiles as $profile)

                                        <option value="{{ $profile['name'] }}">

                                            {{ $profile['name'] }}

                                        </option>

                                    @endforeach

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

                        Simpan PPP Secret

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

                        Username harus unik pada Router MikroTik.

                    </p>

                    <hr>

                    <p>

                        Pilih Profile sesuai paket pelanggan.

                    </p>

                    <hr>

                    <p class="text-muted mb-0">

                        PPP Secret akan langsung dibuat di Router MikroTik setelah disimpan.

                    </p>

                </div>

            </div>

        </div>

    </div>

</form>

@stop