@extends('adminlte::page')

@section('title', 'Edit PPP Profile')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-edit text-warning"></i>

            Edit PPP Profile

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
            href="{{ route('router.pppprofile',$router->id) }}"
            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

    </div>

</div>

@stop

@section('content')

@if(session('error'))

<div class="alert alert-danger">

    <i class="fas fa-ban"></i>

    {{ session('error') }}

</div>

@endif

@if($errors->any())

<div class="alert alert-danger">

    <ul class="mb-0">

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif

<form
    method="POST"
    action="{{ route('router.pppprofile.update', [$router->id, $data['.id']]) }}">

    @csrf

    @method('PUT')

    <div class="row">

        <div class="col-lg-8">

            <div class="card card-warning card-outline shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi PPP Profile

                    </h3>

                </div>

                <div class="card-body">

                    <div class="form-group">

                        <label>Nama Profile</label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $data['name'] ?? '') }}"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Local Address</label>

                        <input
                            type="text"
                            name="local_address"
                            class="form-control"
                            value="{{ old('local_address', $data['local-address'] ?? '') }}">

                    </div>

                    <div class="form-group">

                        <label>Remote Address</label>

                        <input
                            type="text"
                            name="remote_address"
                            class="form-control"
                            value="{{ old('remote_address', $data['remote-address'] ?? '') }}">

                    </div>

                    <div class="row">
                                                <div class="col-md-6">

                            <div class="form-group">

                                <label>Rate Limit</label>

                                <input
                                    type="text"
                                    name="rate_limit"
                                    class="form-control"
                                    value="{{ old('rate_limit', $data['rate-limit'] ?? '') }}"
                                    placeholder="10M/10M">

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Only One</label>

                                <select
                                    name="only_one"
                                    class="form-control">

                                    <option value="no"
                                        {{ old('only_one', $data['only-one'] ?? 'no') == 'no' ? 'selected' : '' }}>
                                        No
                                    </option>

                                    <option value="yes"
                                        {{ old('only_one', $data['only-one'] ?? '') == 'yes' ? 'selected' : '' }}>
                                        Yes
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

                        Simpan Perubahan

                    </button>

                    <a
                        href="{{ route('router.pppprofile',$router->id) }}"
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

                        Perubahan profile akan langsung dikirim ke Router MikroTik.

                    </p>

                    <hr>

                    <p>

                        Pastikan profile tidak sedang digunakan sebelum mengubah parameter penting.

                    </p>

                    <hr>

                    <p class="text-muted mb-0">

                        Gunakan format <strong>Rate Limit</strong> yang sesuai dengan RouterOS.

                    </p>

                </div>

            </div>

        </div>

    </div>

</form>

@stop