<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GNS Network') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body { min-height:100%; }
        body { background-color:#f4f6f9; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; overflow-y:auto; }
        .sidebar {
            width:260px;
            height:100vh;
            background:#0f172a;
            color:#fff;
            position:fixed;
            top:0;
            left:0;
            bottom:0;
            z-index:100;
            overflow-y:auto;
            overflow-x:hidden;
            box-sizing:border-box;
            box-shadow:4px 0 20px rgba(0,0,0,.05);
            scrollbar-width:auto;
        }
        .sidebar::-webkit-scrollbar { width:8px; }
        .sidebar::-webkit-scrollbar-track { background:#0f172a; }
        .sidebar::-webkit-scrollbar-thumb { background:#475569; border-radius:8px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background:#64748b; }
        .main-content { margin-left:260px; min-height:100vh; overflow:visible; padding-bottom:40px; }
        .main-content main { min-height:calc(100vh - 73px); }
        .sidebar .nav-link { color:#94a3b8; padding:11px 16px; border-radius:10px; margin-bottom:6px; font-weight:500; transition:all .2s ease-in-out; }
        .sidebar .nav-link:hover { background-color:rgba(255,255,255,.05); color:#fff; }
        .sidebar .nav-link.active { background:linear-gradient(135deg,#0d6efd 0%,#0b5ed7 100%); color:#fff; box-shadow:0 4px 12px rgba(13,110,253,.3); }
        @media (max-width:768px) { .sidebar{width:100%;height:auto;position:relative;min-height:auto;overflow:visible;} .main-content{margin-left:0;} }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-3 d-none d-md-block">
        <div class="d-flex align-items-center mb-3 px-2">
            <div class="bg-primary text-white p-2 rounded-3 me-2 fw-bold shadow-sm">GNS</div>
            <h5 class="mb-0 fw-bold text-white">GNS Network</h5>
        </div>
        <div class="text-secondary px-2 mb-4" style="font-size:11px;letter-spacing:.5px;">Billing Management System</div>

        <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">Laporan</div>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line me-2"></i> Dashboard Analitik</a></li>
            <li class="nav-item"><a href="{{ route('laporan.index') }}" class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}"><i class="fas fa-chart-bar me-2"></i> Laporan</a></li>
        </ul>

        <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">Master Data</div>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="{{ route('router.index') }}" class="nav-link {{ request()->routeIs('router.*') ? 'active' : '' }}"><i class="fas fa-network-wired me-2"></i> Router</a></li>
            <li class="nav-item"><a href="{{ route('paket.index') }}" class="nav-link {{ request()->routeIs('paket.*') ? 'active' : '' }}"><i class="fas fa-box me-2"></i> Paket Internet</a></li>
            <li class="nav-item"><a href="{{ route('pelanggan.index') }}" class="nav-link {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}"><i class="fas fa-users me-2"></i> Pelanggan</a></li>
        </ul>

        <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">Transaksi</div>
        <ul class="nav flex-column mb-3">
            <li class="nav-item"><a href="{{ route('tagihan.index') }}" class="nav-link {{ request()->routeIs('tagihan.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar me-2"></i> Tagihan</a></li>
            <li class="nav-item"><a href="{{ route('pembayaran.index') }}" class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}"><i class="fas fa-wallet me-2"></i> Pembayaran</a></li>
            <li class="nav-item"><a href="{{ route('whatsapp.index') }}" class="nav-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}"><i class="fab fa-whatsapp me-2"></i> Riwayat WhatsApp</a></li>
        </ul>

        <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">MikroTik</div>
        <ul class="nav flex-column mb-3"><li class="nav-item"><a href="{{ route('mikrotik.monitor') }}" class="nav-link {{ request()->routeIs('mikrotik.*') ? 'active' : '' }}"><i class="fas fa-network-wired me-2"></i> Monitoring MikroTik</a></li></ul>

        <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">System</div>
        <ul class="nav flex-column mb-3">
            @can('user.view')<li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fas fa-users-cog me-2"></i> User Management</a></li>@endcan
            @can('role.view')<li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"><i class="fas fa-user-shield me-2"></i> Role Management</a></li>@endcan
            @can('audit.view')<li class="nav-item"><a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="fas fa-history me-2"></i> Audit Trail</a></li>@endcan
            @can('setting.manage')<li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="fas fa-cogs me-2"></i> Pengaturan</a></li>@endcan
            <li class="nav-item"><a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="fas fa-user me-2"></i> Profile</a></li>
        </ul>

        @role('Super Admin')
            <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">Super Admin</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item"><a href="{{ route('backup.index') }}" class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}"><i class="fas fa-database me-2"></i> Backup Database</a></li>
                <li class="nav-item"><a href="{{ route('restore.index') }}" class="nav-link {{ request()->routeIs('restore.*') ? 'active' : '' }}"><i class="fas fa-upload me-2"></i> Restore Database</a></li>
                <li class="nav-item"><a href="{{ route('superadmin.index') }}" class="nav-link {{ request()->routeIs('superadmin.*') ? 'active' : '' }}"><i class="fas fa-trash-alt me-2"></i> Reset Data</a></li>
            </ul>
        @endrole

        <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size:10px;letter-spacing:.8px;">Account</div>
        <ul class="nav flex-column mb-3"><li class="nav-item"><form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button type="submit" class="nav-link border-0 bg-transparent w-100 text-start"><i class="fas fa-sign-out-alt me-2"></i> Logout</button></form></li></ul>
    </div>

    <div class="main-content w-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 shadow-sm"><div class="container-fluid px-0"><span class="navbar-brand fw-bold fs-6 text-secondary">Billing Management System</span><div class="ms-auto d-flex align-items-center"><span class="fw-semibold text-dark small"><i class="fas fa-user-circle me-1 fs-5 text-primary align-middle"></i>{{ Auth::user()->name ?? 'Administrator' }}</span></div></div></nav>
        <main>@yield('content')</main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
