@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-users-cog text-primary"></i>

            User Management

        </h1>

        <small class="text-muted">

            Kelola user, role, dan hak akses aplikasi GNS Enterprise.

        </small>

    </div>

    <div>

        <a
            href="{{ route('users.create') }}"
            class="btn btn-primary">

            <i class="fas fa-user-plus"></i>

            Tambah User

        </a>

    </div>

</div>

@stop


@section('content')

<div class="card shadow border-0">

    <div class="card-header bg-white">

        <form
            method="GET"
            action="{{ route('users.index') }}">

            <div class="row">

                <div class="col-md-5">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        class="form-control"
                        placeholder="Cari nama atau email...">

                </div>

                <div class="col-md-3">

                    <select
                        name="role"
                        class="form-control">

                        <option value="">

                            Semua Role

                        </option>

                        @foreach($roles as $role)

                            <option
                                value="{{ $role->name }}"
                                @selected(request('role') == $role->name)>

                                {{ $role->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-2">

                    <button
                        class="btn btn-primary btn-block">

                        <i class="fas fa-search"></i>

                        Cari

                    </button>

                </div>

                <div class="col-md-2">

                    <a
                        href="{{ route('users.index') }}"
                        class="btn btn-secondary btn-block">

                        Reset

                    </a>

                </div>

            </div>

        </form>

    </div>

    <div class="card-body p-0">
                <div class="table-responsive">

            <table class="table table-hover table-striped mb-0">

                <thead class="bg-light">

                    <tr>

                        <th width="70">
                            #
                        </th>

                        <th>
                            Nama
                        </th>

                        <th>
                            Email
                        </th>

                        <th width="180">
                            Role
                        </th>

                        <th width="180">
                            Dibuat
                        </th>

                        <th width="220" class="text-center">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($users as $user)

                        <tr>

                            <td>

                                {{ $loop->iteration + ($users->firstItem() - 1) }}

                            </td>

                            <td>

                                <strong>

                                    {{ $user->name }}

                                </strong>

                            </td>

                            <td>

                                {{ $user->email }}

                            </td>

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

                            <td>

                                {{ $user->created_at?->format('d M Y H:i') }}

                            </td>

                            <td class="text-center">

                                <a

                                    href="{{ route('users.show',$user->id) }}"

                                    class="btn btn-info btn-sm">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a

                                    href="{{ route('users.edit',$user->id) }}"

                                    class="btn btn-warning btn-sm">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <form

                                    action="{{ route('users.destroy',$user->id) }}"

                                    method="POST"

                                    class="d-inline">

                                    @csrf

                                    @method('DELETE')
                                    <button

                                        type="submit"

                                        class="btn btn-danger btn-sm"

                                        onclick="return confirm('Yakin ingin menghapus user ini?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-5">

                                <i class="fas fa-users fa-3x text-muted mb-3"></i>

                                <br>

                                <span class="text-muted">

                                    Belum ada data user.

                                </span>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="card-footer bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div class="text-muted">

                Total User :

                <strong>

                    {{ $users->total() }}

                </strong>

            </div>

            <div>

                {{ $users->links() }}

            </div>

        </div>

    </div>

</div>

@stop