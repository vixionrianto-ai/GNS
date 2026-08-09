<style>
    .laporan-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .laporan-kpi-status-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .laporan-kpi-card {
        min-width: 0;
    }

    .laporan-kpi-card .card {
        height: 100%;
        margin-bottom: 0;
    }

    .laporan-kpi-card .card-body {
        min-height: 132px;
    }

    .laporan-kpi-status-grid .card-body {
        min-height: 112px;
    }

    @media (max-width: 1199.98px) {
        .laporan-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .laporan-kpi-grid,
        .laporan-kpi-status-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

{{-- KPI utama --}}
<div class="laporan-kpi-grid">

    {{-- Pendapatan Hari Ini --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-success text-uppercase font-weight-bold">
                            Pendapatan Hari Ini
                        </div>
                        <div class="h4 font-weight-bold mb-2">
                            Rp {{ number_format($pendapatanHariIni,0,',','.') }}
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width:100%"></div>
                        </div>
                        <small class="text-muted">Nilai pembayaran tagihan hari ini</small>
                    </div>
                    <i class="fas fa-wallet fa-2x text-success ml-3"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Pendapatan Bulan --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-primary text-uppercase font-weight-bold">
                            Pendapatan Bulan
                        </div>
                        <div class="h4 font-weight-bold mb-2">
                            Rp {{ number_format($pendapatanBulanIni,0,',','.') }}
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min($persenLunas, 100) }}%"></div>
                        </div>
                        <small class="text-muted">{{ $persenLunas }}% dari total tagihan telah dibayar</small>
                    </div>
                    <i class="fas fa-chart-line fa-2x text-primary ml-3"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Kas Masuk --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-info text-uppercase font-weight-bold">
                            Kas Masuk Bulan
                        </div>
                        <div class="h4 font-weight-bold mb-2">
                            Rp {{ number_format($kasMasukBulanIni,0,',','.') }}
                        </div>
                        <small class="text-muted d-block">
                            Tagihan Rp {{ number_format($pendapatanBulanIni,0,',','.') }}
                        </small>
                        <small class="text-muted d-block">
                            Admin Rp {{ number_format($biayaAdminBulanIni,0,',','.') }}
                            &middot; Saldo Rp {{ number_format($saldoMasukBulanIni,0,',','.') }}
                        </small>
                    </div>
                    <i class="fas fa-cash-register fa-2x text-info ml-3"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Tagihan --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-dark shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-dark text-uppercase font-weight-bold">
                            Total Tagihan
                        </div>
                        <div class="h4 font-weight-bold">
                            Rp {{ number_format($totalTagihan,0,',','.') }}
                        </div>
                    </div>
                    <i class="fas fa-file-invoice fa-2x text-dark ml-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KPI tagihan dan status --}}
<div class="laporan-kpi-status-grid">

    {{-- Piutang --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-warning text-uppercase font-weight-bold">Piutang</div>
                        <div class="h4 font-weight-bold mb-2">Rp {{ number_format($piutang,0,',','.') }}</div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min($persenPiutang, 100) }}%"></div>
                        </div>
                        <small class="text-muted">{{ $persenPiutang }}% dari total tagihan</small>
                    </div>
                    <i class="fas fa-file-invoice-dollar fa-2x text-warning ml-3"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Pelanggan Aktif --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-info shadow h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-users fa-2x text-info mb-2"></i>
                <h3 class="mb-1">{{ $pelangganAktif }}</h3>
                <small class="text-muted">Pelanggan Aktif</small>
            </div>
        </div>
    </div>

    {{-- Lunas --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-success shadow h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h3 class="mb-1">{{ $totalLunas }}</h3>
                <small class="text-muted">Lunas</small>
            </div>
        </div>
    </div>

    {{-- Sebagian --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-adjust fa-2x text-warning mb-2"></i>
                <h3 class="mb-1">{{ $totalSebagian }}</h3>
                <small class="text-muted">Sebagian</small>
            </div>
        </div>
    </div>

    {{-- Belum Bayar --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-secondary shadow h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-clock fa-2x text-secondary mb-2"></i>
                <h3 class="mb-1">{{ $totalBelumBayar }}</h3>
                <small class="text-muted">Belum Bayar</small>
            </div>
        </div>
    </div>

    {{-- Jatuh Tempo --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-danger shadow h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h3 class="mb-1">{{ $totalJatuhTempo }}</h3>
                <small class="text-muted">Jatuh Tempo</small>
            </div>
        </div>
    </div>

</div>