@extends('adminlte::page')

@section('title', 'Profile')

@section('content_header')
<div class="mb-2">
    <h1 class="mb-1 font-weight-bold text-dark" style="font-size:1.35rem;">
        <i class="fas fa-user-circle text-primary mr-2"></i>Profile
    </h1>
    <small class="text-muted" style="font-size:11px;">Kelola informasi akun, password, dan keamanan profile Anda.</small>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline shadow-sm rounded-4 border-0 mb-2">
            <div class="card-header bg-white py-2 border-0">
                <h3 class="card-title font-weight-bold text-dark m-0" style="font-size:.95rem;">
                    <i class="fas fa-user-edit text-primary mr-2"></i>Informasi Profile
                </h3>
            </div>
            <div class="card-body p-3">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card card-warning card-outline shadow-sm rounded-4 border-0 mb-2">
            <div class="card-header bg-white py-2 border-0">
                <h3 class="card-title font-weight-bold text-dark m-0" style="font-size:.95rem;">
                    <i class="fas fa-lock text-warning mr-2"></i>Update Password
                </h3>
            </div>
            <div class="card-body p-3">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="card card-danger card-outline shadow-sm rounded-4 border-0 mb-2">
            <div class="card-header bg-white py-2 border-0">
                <h3 class="card-title font-weight-bold text-dark m-0" style="font-size:.95rem;">
                    <i class="fas fa-user-times text-danger mr-2"></i>Hapus Akun
                </h3>
            </div>
            <div class="card-body p-3">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-primary card-outline shadow-sm rounded-4 border-0">
            <div class="card-body text-center p-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:70px;height:70px;font-size:28px;">
                    <i class="fas fa-user"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-1">{{ Auth::user()->name }}</h5>
                <p class="text-muted mb-3 small">{{ Auth::user()->email }}</p>
                <span class="badge badge-primary px-3 py-2">
                    {{ Auth::user()->getRoleNames()->implode(', ') ?: 'Tanpa Role' }}
                </span>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card{border-radius:12px;}
.card-header{border-radius:12px 12px 0 0 !important;}
.btn{border-radius:6px;}
.form-control{border-radius:6px;}
</style>
@stop
