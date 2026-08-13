<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GNS Network') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        /* Sidebar Elegan & Profesional */
        .sidebar { 
            width: 260px; 
            min-height: 100vh; 
            background: #0f172a; /* Warna Slate Modern Dark yang berkelas */
            color: #fff; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 100; 
            overflow-y: auto; 
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
        }
        
        .main-content { 
            margin-left: 260px; 
            min-height: 100vh; 
        }
        
        .sidebar .nav-link { 
            color: #94a3b8; 
            padding: 11px 16px; 
            border-radius: 10px; 
            margin-bottom: 6px; 
            font-weight: 500; 
            transition: all 0.2s ease-in-out;
        }
        
        .sidebar .nav-link:hover { 
            background-color: rgba(255, 255, 255, 0.05); 
            color: #fff; 
        }
        
        .sidebar .nav-link.active { 
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); 
            color: #fff; 
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; min-height: auto; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <div class="d-flex">
        <!-- Sidebar Navigasi Lengkap -->
        <div class="sidebar p-3 d-none d-md-block">
            <div class="d-flex align-items-center mb-3 px-2">
                <div class="bg-primary text-white p-2 rounded-3 me-2 fw-bold shadow-sm">GNS</div>
                <h5 class="mb-0 fw-bold text-white">GNS Network</h5>
            </div>
            <div class="text-secondary px-2 mb-4" style="font-size: 11px; letter-spacing: 0.5px;">Billing Management System</div>

            <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size: 10px; letter-spacing: 0.8px;">Master Data</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a href="{{ route('router.index') }}" class="nav-link {{ request()->routeIs('router.*') ? 'active' : '' }}"><i class="fas fa-network-wired me-2"></i> Router</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('paket.index') }}" class="nav-link {{ request()->routeIs('paket.*') ? 'active' : '' }}"><i class="fas fa-box me-2"></i> Paket Internet</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pelanggan.index') }}" class="nav-link {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}"><i class="fas fa-users me-2"></i> Pelanggan</a>
                </li>
            </ul>

            <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size: 10px; letter-spacing: 0.8px;">Transaksi</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a href="{{ route('tagihan.index') }}" class="nav-link {{ request()->routeIs('tagihan.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar me-2"></i> Tagihan</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pembayaran.index') }}" class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}"><i class="fas fa-wallet me-2"></i> Pembayaran</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link"><i class="fab fa-whatsapp me-2"></i> Riwayat WhatsApp</a>
                </li>
            </ul>

            <div class="text-secondary text-uppercase fw-bold px-2 mb-2" style="font-size: 10px; letter-spacing: 0.8px;">Laporan</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link"><i class="fas fa-chart-line me-2"></i> Dashboard Analitik</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Laporan</a>
                </li>
            </ul>
        </div>

        <!-- Konten Utama -->
        <div class="main-content w-100">
            <!-- TopNavbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 shadow-sm">
                <div class="container-fluid px-0">
                    <span class="navbar-brand fw-bold fs-6 text-secondary">Billing Management System</span>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle me-2 fs-4 text-primary"></i>

                            <div class="lh-sm">
                                <div class="fw-semibold text-dark small">
                                    {{ auth()->user()->name }}
                                </div>

                                <div class="text-muted" style="font-size: 11px;">
                                    {{ auth()->user()->getRoleNames()->first() ?? 'Belum ada role' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Bagian Konten Halaman -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>