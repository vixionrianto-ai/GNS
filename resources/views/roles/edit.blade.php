@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-user-edit text-warning"></i>

            Edit Role

        </h1>

        <small class="text-muted">

            Perbarui data role beserta permission.

        </small>

    </div>

    <div>

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

<form
    action="{{ route('roles.update',$role->id) }}"
    method="POST">

    @csrf
    @method('PUT')

    <div class="row">

        <div class="col-lg-4">

            <div class="card shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Informasi Role

                    </h3>

                </div>

                <div class="card-body">

                    <div class="form-group">

                        <label>

                            Nama Role

                        </label>

                        <input

                            type="text"

                            name="name"

                            value="{{ old('name',$role->name) }}"

                            class="form-control @error('name') is-invalid @enderror">

                        @error('name')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-8">

            <div class="card shadow">

                <div class="card-header">

                    <h3 class="card-title">

                        Permission

                    </h3>

                </div>

                <div class="card-body">
                                        @php

                        $grouped = $permissions->groupBy(function($permission){

                            return ucfirst(
                                explode('.', $permission->name)[0]
                            );

                        });

                    @endphp

                    @foreach($grouped as $module => $items)

                        <div class="card card-outline card-primary mb-3">

                            <div class="card-header">

                                <strong>

                                    {{ $module }}

                                </strong>

                            </div>

                            <div class="card-body">

                                <div class="row">

                                    @foreach($items as $permission)

                                        <div class="col-md-4">

                                            <div class="form-check mb-2">

                                                <input

                                                    class="form-check-input"

                                                    type="checkbox"

                                                    name="permissions[]"

                                                    value="{{ $permission->name }}"

                                                    id="permission{{ $permission->id }}"

                                                    {{ in_array($permission->name, old('permissions',$role->permissions->pluck('name')->toArray())) ? 'checked' : '' }}>

                                                <label

                                                    class="form-check-label"

                                                    for="permission{{ $permission->id }}">

                                                    {{ $permission->name }}

                                                </label>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    @endforeach
                                    </div>

                <div class="card-footer">

                    <button

                        type="submit"

                        class="btn btn-warning">

                        <i class="fas fa-save"></i>

                        Update Role

                    </button>

                    <a

                        href="{{ route('roles.index') }}"

                        class="btn btn-secondary">

                        Batal

                    </a>

                </div>

            </div>

        </div>

    </div>

</form>

@stop