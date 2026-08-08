@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-user-edit text-warning"></i>

            Edit User

        </h1>

        <small class="text-muted">

            Perbarui data pengguna GNS Enterprise.

        </small>

    </div>

    <div>

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

    <div class="col-lg-8">

        <div class="card shadow border-0">

            <div class="card-header bg-white">

                <h3 class="card-title">

                    Informasi User

                </h3>

            </div>

            <form
                action="{{ route('users.update',$user->id) }}"
                method="POST">

                @csrf

                @method('PUT')

                <div class="card-body">
                                        <div class="form-group">

                        <label>

                            Nama Lengkap

                        </label>

                        <input

                            type="text"

                            name="name"

                            class="form-control @error('name') is-invalid @enderror"

                            value="{{ old('name', $user->name) }}"

                            placeholder="Masukkan nama lengkap">

                        @error('name')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>



                    <div class="form-group">

                        <label>

                            Email

                        </label>

                        <input

                            type="email"

                            name="email"

                            class="form-control @error('email') is-invalid @enderror"

                            value="{{ old('email', $user->email) }}"

                            placeholder="Masukkan alamat email">

                        @error('email')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>



                    <div class="form-group">

                        <label>

                            Role

                        </label>

                        <select

                            name="role"

                            class="form-control @error('role') is-invalid @enderror">

                            <option value="">

                                -- Pilih Role --

                            </option>

                            @foreach($roles as $role)

                                <option

                                    value="{{ $role->name }}"

                                    @selected(old('role', $user->roles->first()?->name) == $role->name)>

                                    {{ $role->name }}

                                </option>

                            @endforeach

                        </select>

                        @error('role')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>



                    <hr>



                    <div class="alert alert-warning">

                        <i class="fas fa-info-circle"></i>

                        Kosongkan password apabila tidak ingin mengubah password user.

                    </div>



                    <div class="row">

                        <div class="col-md-6">

                            <div class="form-group">

                                <label>

                                    Password Baru

                                </label>

                                <input

                                    type="password"

                                    name="password"

                                    class="form-control @error('password') is-invalid @enderror"

                                    placeholder="Masukkan password baru">

                                @error('password')

                                    <div class="invalid-feedback">

                                        {{ $message }}

                                    </div>

                                @enderror

                            </div>

                        </div>



                        <div class="col-md-6">

                            <div class="form-group">

                                <label>

                                    Konfirmasi Password

                                </label>

                                <input

                                    type="password"

                                    name="password_confirmation"

                                    class="form-control"

                                    placeholder="Ulangi password baru">

                            </div>

                        </div>

                    </div>
                                    </div>

                <div class="card-footer bg-white">

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="fas fa-save"></i>

                        Update User

                    </button>

                    <a
                        href="{{ route('users.index') }}"
                        class="btn btn-secondary">

                        <i class="fas fa-times"></i>

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card shadow border-0">

            <div class="card-header bg-white">

                <h3 class="card-title">

                    Informasi User

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-sm">

                    <tr>

                        <th width="40%">

                            ID

                        </th>

                        <td>

                            {{ $user->id }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Role Aktif

                        </th>

                        <td>

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

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Dibuat

                        </th>

                        <td>

                            {{ $user->created_at?->format('d M Y H:i') }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Diubah

                        </th>

                        <td>

                            {{ $user->updated_at?->format('d M Y H:i') }}

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

                <hr>

                <div class="alert alert-info mb-0">

                    <i class="fas fa-info-circle"></i>

                    Password hanya akan berubah apabila kolom
                    <strong>Password Baru</strong> diisi.

                </div>

            </div>

        </div>

    </div>

</div>

@stop