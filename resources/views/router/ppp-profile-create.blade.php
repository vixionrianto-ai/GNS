@extends('adminlte::page')

@section('title', 'Tambah PPP Profile')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-plus-circle text-primary"></i>

            Tambah PPP Profile

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
    action="{{ route('router.pppprofile.store',$router->id) }}">

    @csrf

    <div class="row">

        <div class="col-lg-8">

            <div class="card card-primary card-outline shadow">

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
                            value="{{ old('name') }}"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Local Address</label>

                        <input
                            type="text"
                            name="local_address"
                            class="form-control"
                            value="{{ old('local_address') }}">

                    </div>

                    <div class="form-group">

                        <label>Remote Address</label>

                        <input
                            type="text"
                            name="remote_address"
                            class="form-control"
                            value="{{ old('remote_address') }}">

                    </div>

                    <div class="row">
                                                <div class="col-md-6">

                            <div class="form-group">

                                <label>Rate Limit</label>

                                <input
                                    type="text"
                                    name="rate_limit"
                                    class="form-control"
                                    placeholder="10M/10M"
                                    value="{{ old('rate_limit') }}">

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>Only One</label>

                                <select
                                    name="only_one"
                                    class="form-control">

                                    <option value="no"
                                        {{ old('only_one') == 'no' ? 'selected' : '' }}>

                                        No

                                    </option>

                                    <option value="yes"
                                        {{ old('only_one') == 'yes' ? 'selected' : '' }}>

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
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Simpan Profile

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

                        Nama profile harus unik pada router.

                    </p>

                    <hr>

                    <p>

                        Gunakan format Rate Limit seperti:

                        <strong>10M/10M</strong>

                    </p>

                    <hr>

                    <p class="text-muted mb-0">

                        Profile ini nantinya dapat dipilih saat membuat PPP Secret pelanggan.

                    </p>

                </div>

            </div>

        </div>

    </div>

</form>

@stop
