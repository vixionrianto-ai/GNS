<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="fas fa-network-wired me-2"></i>GNS Network
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @can('dashboard.view')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                @endcan

                @can('router.view')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('router.*') ? 'active' : '' }}" href="{{ route('router.index') }}"><i class="fas fa-network-wired me-1"></i>Router</a></li>
                @endcan

                @can('paket.view')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('paket.*') ? 'active' : '' }}" href="{{ route('paket.index') }}"><i class="fas fa-box me-1"></i>Paket</a></li>
                @endcan

                @can('pelanggan.view')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}" href="{{ route('pelanggan.index') }}"><i class="fas fa-users me-1"></i>Pelanggan</a></li>
                @endcan

                @can('tagihan.view')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('tagihan.*') ? 'active' : '' }}" href="{{ route('tagihan.index') }}"><i class="fas fa-file-invoice-dollar me-1"></i>Tagihan</a></li>
                @endcan

                @can('pembayaran.view')
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}"><i class="fas fa-wallet me-1"></i>Pembayaran</a></li>
                @endcan

                @if(auth()->user()->can('user.view') || auth()->user()->can('role.view') || auth()->user()->can('audit.view'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs me-1"></i>Administrasi
                        </a>
                        <ul class="dropdown-menu">
                            @can('user.view')
                                <li><a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="fas fa-users-cog me-2"></i>User Management</a></li>
                            @endcan
                            @can('role.view')
                                <li><a class="dropdown-item {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="fas fa-user-shield me-2"></i>Role Management</a></li>
                            @endcan
                            @can('audit.view')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}"><i class="fas fa-history me-2"></i>Audit Trail</a></li>
                            @endcan
                        </ul>
                    </li>
                @endif
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fs-5 me-2"></i>{{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <div class="dropdown-header">
                                <strong>{{ Auth::user()->name }}</strong><br>
                                <small class="text-muted">{{ Auth::user()->getRoleNames()->implode(', ') ?: 'Tanpa Role' }}</small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Log Out</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>