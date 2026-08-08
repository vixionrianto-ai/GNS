@extends('adminlte::page')

@section('title', 'PPP Profile')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1>

            <i class="fas fa-id-card text-info"></i>

            PPP Profile

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
            href="{{ route('router.index') }}"
            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

        <a
            href="{{ route('router.pppprofile.create',$router->id) }}"
            class="btn btn-primary">

            <i class="fas fa-plus-circle"></i>

            Tambah PPP Profile

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

            Data PPP Profile

        </h3>

    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-hover table-striped">

            <thead>

                <tr>

                    <th width="60">#</th>

                    <th>Profile</th>

                    <th>Local Address</th>

                    <th>Remote Address</th>

                    <th>Rate Limit</th>

                    <th>Only One</th>

                    <th width="180">

                        Aksi

                    </th>

                </tr>

            </thead>

            <tbody>
                @forelse($profiles as $profile)

<tr>

    <td>

        {{ $loop->iteration }}

    </td>

    <td>

        <strong>

            {{ $profile['name'] ?? '-' }}

        </strong>

    </td>

    <td>

        {{ $profile['local-address'] ?? '-' }}

    </td>

    <td>

        {{ $profile['remote-address'] ?? '-' }}

    </td>

    <td>

        <span class="badge badge-primary">

            {{ $profile['rate-limit'] ?? '-' }}

        </span>

    </td>

    <td>

        @if(($profile['only-one'] ?? 'no') == 'yes')

            <span class="badge badge-success">

                Yes

            </span>

        @else

            <span class="badge badge-secondary">

                No

            </span>

        @endif

    </td>

    <td>

        <div class="btn-group">

            <a

                href="{{ route('router.pppprofile.edit', [$router->id, $profile['.id']]) }}"

                class="btn btn-warning btn-sm"

                title="Edit">

                <i class="fas fa-edit"></i>

            </a>

            <form

                action="{{ route('router.pppprofile.delete', [$router->id, $profile['.id']]) }}"

                method="POST"

                class="d-inline"

                onsubmit="return confirm('Hapus PPP Profile {{ $profile['name'] }} ?')">

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

        <i class="fas fa-id-card fa-3x text-muted mb-3"></i>

        <br>

        <span class="text-muted">

            Belum ada PPP Profile.

        </span>

    </td>

</tr>

@endforelse
            </tbody>

        </table>

    </div>

    <div class="card-footer">

        <div class="row">

            <div class="col-md-6">

                <small class="text-muted">

                    Total Profile :

                    <strong>

                        {{ count($profiles) }}

                    </strong>

                </small>

            </div>

            <div class="col-md-6 text-right">

                <small class="text-muted">

                    {{ $router->nama_router }}

                </small>

            </div>

        </div>

    </div>

</div>

@stop