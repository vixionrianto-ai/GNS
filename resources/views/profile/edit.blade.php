@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="fas fa-user-circle text-primary me-2"></i>Profile
            </h1>
            <p class="text-muted mb-0">Kelola informasi akun, password, dan keamanan profile Anda.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit text-primary me-2"></i>Informasi Profile</h5>
                </div>
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-lock text-warning me-2"></i>Update Password</h5>
                </div>
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-danger"><i class="fas fa-user-times me-2"></i>Hapus Akun</h5>
                </div>
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:32px;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted mb-3">{{ Auth::user()->email }}</p>
                    <span class="badge bg-primary px-3 py-2">
                        {{ Auth::user()->getRoleNames()->implode(', ') ?: 'Tanpa Role' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection