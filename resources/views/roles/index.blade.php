@extends('adminlte::page')

@section('title', 'Role Management')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-user-shield text-primary"></i>

            Role Management

        </h1>

        <small class="text-muted">

            Kelola Role dan Hak Akses GNS Enterprise.

        </small>

    </div>

    <div>

        <a
            href="{{ route('roles.create') }}"
            class="btn btn-primary">

            <i class="fas fa-plus-circle"></i>

            Tambah Role

        </a>

    </div>

</div>

@stop


@section('content')

<div class="card shadow border-0">

    <div class="card-header bg-white">

        <form
            method="GET"
            action="{{ route('roles.index') }}">

            <div class="row">

                <div class="col-md-10">

                    <input

                        type="text"

                        name="search"

                        value="{{ request('search') }}"

                        class="form-control"

                        placeholder="Cari nama role...">

                </div>

                <div class="col-md-2">

                    <button
                        class="btn btn-primary btn-block">

                        <i class="fas fa-search"></i>

                        Cari

                    </button>

                </div>

            </div>

        </form>

    </div>

    <div class="card-body p-0">
                <div class="table-responsive">

            <table class="table table-hover table-striped mb-0">

                <thead class="bg-light">

                    <tr>

                        <th width="60">
                            #
                        </th>

                        <th>
                            Nama Role
                        </th>

                        <th width="150" class="text-center">
                            Total User
                        </th>

                        <th width="170" class="text-center">
                            Total Permission
                        </th>

                        <th width="180">
                            Dibuat
                        </th>

                        <th width="240" class="text-center">
                            Aksi
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($roles as $role)

                        <tr>

                            <td>

                                {{ $loop->iteration + ($roles->firstItem() - 1) }}

                            </td>

                            <td>

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

                            </td>

                            <td class="text-center">

                                <span class="badge badge-info">

                                    {{ $role->users_count }}

                                </span>

                            </td>

                            <td class="text-center">

                                <span class="badge badge-success">

                                    {{ $role->permissions_count }}

                                </span>

                            </td>

                            <td>

                                {{ $role->created_at?->format('d M Y H:i') }}

                            </td>

                            <td class="text-center">

                                <a

                                    href="{{ route('roles.show',$role->id) }}"

                                    class="btn btn-info btn-sm"

                                    title="Detail">

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a

                                    href="{{ route('roles.edit',$role->id) }}"

                                    class="btn btn-warning btn-sm"

                                    title="Edit">

                                    <i class="fas fa-edit"></i>

                                </a>

                                <a

                                    href="{{ route('roles.edit',$role->id) }}"

                                    class="btn btn-success btn-sm"

                                    title="Permission">

                                    <i class="fas fa-key"></i>

                                </a>

                                @if($role->name != 'Super Admin')

                                <form

                                    action="{{ route('roles.destroy',$role->id) }}"

                                    method="POST"

                                    class="d-inline">

                                    @csrf

                                    @method('DELETE')
                                                                        <button

                                        type="submit"

                                        class="btn btn-danger btn-sm"

                                        onclick="return confirm('Yakin ingin menghapus role ini?')">

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-5">

                                <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>

                                <br>

                                <span class="text-muted">

                                    Belum ada data role.

                                </span>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="card-footer bg-white">

        <div class="row align-items-center">

            <div class="col-md-6">

                <small class="text-muted">

                    Total Role :

                    <strong>

                        {{ $roles->total() }}

                    </strong>

                </small>

            </div>

            <div class="col-md-6 d-flex justify-content-end">

                {{ $roles->links() }}

            </div>

        </div>

    </div>

</div>

@stop