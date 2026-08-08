@extends('adminlte::page')

@section('title', 'Detail Role')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-user-shield text-info"></i>

            Detail Role

        </h1>

        <small class="text-muted">

            Informasi lengkap role dan permission.

        </small>

    </div>

    <div>

        <a
            href="{{ route('roles.edit',$role->id) }}"
            class="btn btn-warning">

            <i class="fas fa-edit"></i>

            Edit

        </a>

        <a
            href="{{ route('roles.index') }}"
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

        <div class="card shadow">

            <div class="card-body text-center">

                <i class="fas fa-user-shield fa-6x text-primary mb-3"></i>

                <h3>

                    {{ $role->name }}

                </h3>

                <span class="badge badge-primary">

                    {{ $role->name }}

                </span>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-header">

                <h3 class="card-title">

                    Informasi

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-sm">

                    <tr>

                        <th width="40%">

                            ID

                        </th>

                        <td>

                            {{ $role->id }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Guard

                        </th>

                        <td>

                            {{ $role->guard_name }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            User

                        </th>

                        <td>

                            {{ $role->users->count() }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Permission

                        </th>

                        <td>

                            {{ $role->permissions->count() }}

                        </td>

                    </tr>

                    <tr>

                        <th>

                            Dibuat

                        </th>

                        <td>

                            {{ $role->created_at?->format('d M Y H:i') }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

    <div class="col-lg-8">

        <div class="card shadow">

            <div class="card-header">

                <h3 class="card-title">

                    Daftar Permission

                </h3>

            </div>

            <div class="card-body">
                @php

$grouped = $role->permissions->groupBy(function($permission){

    return ucfirst(

        explode('.', $permission->name)[0]

    );

});

@endphp

@if($grouped->count())

    @foreach($grouped as $module => $permissions)

        <div class="card card-outline card-primary mb-3">

            <div class="card-header">

                <strong>

                    {{ $module }}

                </strong>

            </div>

            <div class="card-body">

                <div class="row">

                    @foreach($permissions as $permission)

                        <div class="col-md-4 mb-2">

                            <span class="badge badge-success">

                                <i class="fas fa-check"></i>

                                {{ $permission->name }}

                            </span>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>

    @endforeach

@else

    <div class="alert alert-warning">

        Role ini belum memiliki permission.

    </div>

@endif
            </div>

        </div>

    </div>

</div>

@stop