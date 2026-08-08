@extends('adminlte::page')

@section('title', 'Detail User')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-user text-info"></i>

            Detail User

        </h1>

        <small class="text-muted">

            Informasi lengkap pengguna GNS Enterprise.

        </small>

    </div>

    <div>

        <a
            href="{{ route('users.edit', $user->id) }}"
            class="btn btn-warning">

            <i class="fas fa-edit"></i>

            Edit

        </a>

        <a
            href="{{ route('users.index') }}"
            class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>

            Kembali

        </a>

    </div>

</div>

@stop

@section('content')

<div class="row">

    <div class="col-lg-4">

        <div class="card shadow border-0">

            <div class="card-body text-center">

                <div class="mb-3">

                    <i class="fas fa-user-circle fa-6x text-primary"></i>

                </div>

                <h3>

                    {{ $user->name }}

                </h3>

                <p class="text-muted">

                    {{ $user->email }}

                </p>

                @forelse($user->roles as $role)

                    @php

                        $badge = match($role->name){

                            'Super Admin' => 'danger',

                            'Admin' => 'primary',

                            'Kasir' => 'success',

                            'Teknisi' => 'warning',

                            default => 'secondary'

                        };

                    @endphp

                    <span class="badge badge-{{ $badge }}">

                        {{ $role->name }}

                    </span>

                @empty

                    <span class="badge badge-secondary">

                        Tidak Ada Role

                    </span>

                @endforelse

            </div>

        </div>

    </div>

    <div class="col-lg-8">

        <div class="card shadow border-0">

            <div class="card-header bg-white">

                <h3 class="card-title">

                    Informasi User

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-bordered">
                                        <tr>

                        <th width="30%">

                            ID

                        </th>

                        <td>

                            {{ $user->id }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Nama Lengkap

                        </th>

                        <td>

                            {{ $user->name }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Email

                        </th>

                        <td>

                            {{ $user->email }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Role

                        </th>

                        <td>

                            @foreach($user->roles as $role)

                                @php

                                    $badge = match($role->name){

                                        'Super Admin' => 'danger',

                                        'Admin' => 'primary',

                                        'Kasir' => 'success',

                                        'Teknisi' => 'warning',

                                        default => 'secondary'

                                    };

                                @endphp

                                <span class="badge badge-{{ $badge }}">

                                    {{ $role->name }}

                                </span>

                            @endforeach

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Dibuat

                        </th>

                        <td>

                            {{ $user->created_at?->format('d F Y H:i:s') }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Terakhir Diubah

                        </th>

                        <td>

                            {{ $user->updated_at?->format('d F Y H:i:s') }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Status

                        </th>

                        <td>

                            <span class="badge badge-success">

                                Aktif

                            </span>

                        </td>

                    </tr>

                </table>

            </div>

        </div>
                <div class="card shadow border-0">

            <div class="card-header bg-white">

                <h3 class="card-title">

                    Ringkasan

                </h3>

            </div>

            <div class="card-body">

                <div class="row text-center">

                    <div class="col-md-4">

                        <h4>

                            {{ $user->roles->count() }}

                        </h4>

                        <small class="text-muted">

                            Total Role

                        </small>

                    </div>

                    <div class="col-md-4">

                        <h4>

                            {{ $user->created_at?->diffForHumans() }}

                        </h4>

                        <small class="text-muted">

                            Bergabung

                        </small>

                    </div>

                    <div class="col-md-4">

                        <span class="badge badge-success p-2">

                            ACTIVE

                        </span>

                        <br>

                        <small class="text-muted">

                            Status Akun

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@stop