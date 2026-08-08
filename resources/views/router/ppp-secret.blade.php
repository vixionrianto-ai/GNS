@extends('adminlte::page')

@section('title', 'PPP Secret')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-user-lock text-primary"></i>

            PPP Secret

        </h1>

        <small class="text-muted">

            Router :

            <strong>

                {{ $router->nama_router }}

            </strong>

        </small>

    </div>

    <div>

        <a href="{{ route('router.index') }}"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

        <a href="{{ route('router.pppsecret.create',$router->id) }}"
           class="btn btn-primary">

            <i class="fas fa-plus-circle"></i>

            Tambah PPP Secret

        </a>

    </div>

</div>

@stop


@section('content')

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show">

    <i class="fas fa-check-circle"></i>

    {{ session('success') }}

    <button class="close" data-dismiss="alert">

        &times;

    </button>

</div>

@endif


@if(session('error'))

<div class="alert alert-danger alert-dismissible fade show">

    <i class="fas fa-times-circle"></i>

    {{ session('error') }}

    <button class="close" data-dismiss="alert">

        &times;

    </button>

</div>

@endif


<div class="card shadow">

    <div class="card-header">

        <h3 class="card-title">

            Data PPP Secret

        </h3>

    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-hover table-striped">

            <thead>

                <tr>

                    <th width="60">#</th>

                    <th>Username</th>

                    <th>Password</th>

                    <th>Service</th>

                    <th>Profile</th>

                    <th>Status</th>

                    <th width="260">

                        Aksi

                    </th>

                </tr>

            </thead>

            <tbody>
                @forelse($secrets as $secret)

<tr>

    <td>

        {{ $loop->iteration }}

    </td>

    <td>

        <strong>

            {{ $secret['name'] ?? '-' }}

        </strong>

    </td>

    <td>

        <code>

            {{ $secret['password'] ?? '-' }}

        </code>

    </td>

    <td>

        <span class="badge badge-info">

            {{ strtoupper($secret['service'] ?? '-') }}

        </span>

    </td>

    <td>

        <span class="badge badge-primary">

            {{ $secret['profile'] ?? '-' }}

        </span>

    </td>

    <td>

        @if(($secret['disabled'] ?? 'false') == 'true')

            <span class="badge badge-danger">

                Disabled

            </span>

        @else

            <span class="badge badge-success">

                Aktif

            </span>

        @endif

    </td>

    <td>

        <div class="btn-group">

            <a

                href="{{ route('router.pppsecret.edit', [$router->id, $secret['name']]) }}"

                class="btn btn-warning btn-sm"

                title="Edit">

                <i class="fas fa-edit"></i>

            </a>

            @if(($secret['disabled'] ?? 'false') == 'true')

                <form

                    action="{{ route('router.pppsecret.enable', [$router->id, $secret['.id']]) }}"

                    method="POST"

                    class="d-inline">

                    @csrf

                    @method('PUT')

                    <button

                        type="submit"

                        class="btn btn-success btn-sm"

                        title="Enable">

                        <i class="fas fa-play"></i>

                    </button>

                </form>

            @else

                <form

                    action="{{ route('router.pppsecret.disable', [$router->id, $secret['.id']]) }}"

                    method="POST"

                    class="d-inline">

                    @csrf

                    @method('PUT')

                    <button

                        type="submit"

                        class="btn btn-secondary btn-sm"

                        title="Disable">

                        <i class="fas fa-pause"></i>

                    </button>

                </form>

            @endif

            <form

                action="{{ route('router.pppsecret.delete', [$router->id, $secret['.id']]) }}"

                method="POST"

                class="d-inline"

                onsubmit="return confirm('Hapus PPP Secret {{ $secret['name'] }} ?')">

                @csrf

                @method('DELETE')

                <button

                    type="submit"

                    class="btn btn-danger btn-sm"

                    title="Hapus">

                    <i class="fas fa-trash"></i>

                </button>

            </form>

        </div>

    </td>

</tr>

@empty

<tr>

    <td colspan="7" class="text-center py-5">

        <i class="fas fa-user-lock fa-3x text-muted mb-3"></i>

        <br>

        <span class="text-muted">

            Belum ada PPP Secret.

        </span>

    </td>

</tr>

@endforelse
            </tbody>

        </table>

    </div>

    <div class="card-footer">

        <div class="row align-items-center">

            <div class="col-md-6">

                <small class="text-muted">

                    Total PPP Secret :

                    <strong>

                        {{ count($secrets) }}

                    </strong>

                </small>

            </div>

            <div class="col-md-6 text-right">

                <small class="text-muted">

                    Router :

                    <strong>

                        {{ $router->nama_router }}

                    </strong>

                </small>

            </div>

        </div>

    </div>

</div>

@stop


@section('css')

<style>

.table td{

    vertical-align: middle;

}

.badge{

    font-size:12px;

}

.btn-group .btn{

    margin-right:2px;

}

code{

    font-size:13px;

}

</style>

@stop


@section('js')

<script>

$(function(){

    $('[title]').tooltip();

});

</script>

@stop