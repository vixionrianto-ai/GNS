<style>
    .laporan-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .laporan-summary {
        background: #fff;
        border-radius: .85rem;
        box-shadow: 0 .15rem 1.25rem rgba(0,0,0,.06);
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    .laporan-summary-title {
        display: flex;
        align-items: center;
        gap: .65rem;
        font-size: .82rem;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 1rem;
    }

    .laporan-summary-title::before {
        content: '';
        width: 4px;
        height: 22px;
        border-radius: 99px;
        background: #0d6efd;
    }

    .laporan-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .laporan-summary-item {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 0;
        padding: .35rem 1rem;
        border-right: 1px solid #e9ecef;
    }

    .laporan-summary-item:first-child { padding-left: 0; }
    .laporan-summary-item:last-child { border-right: 0; padding-right: 0; }

    .laporan-summary-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f3f6fa;
    }

    .laporan-summary-value {
        font-size: 1.25rem;
        line-height: 1.1;
        font-weight: 700;
        color: #1f2937;
    }

    .laporan-summary-label {
        font-size: .76rem;
        color: #6b7280;
        margin-top: .2rem;
    }

    .laporan-kpi-card { min-width: 0; }

    .laporan-kpi-card .card {
        height: 100%;
        margin-bottom: 0;
        border-radius: .85rem;
        border-top: 0;
        box-shadow: 0 .15rem 1.25rem rgba(0,0,0,.06) !important;
    }

    .laporan-kpi-card .card-body { min-height: 148px; }

    .laporan-kpi-value {
        font-size: 1.45rem;
        line-height: 1.15;
        white-space: nowrap;
    }

    @media (max-width: 1199.98px) {
        .laporan-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .laporan-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .laporan-summary-item:nth-child(3) { border-right: 0; }
        .laporan-summary-item:nth-child(4),
        .laporan-summary-item:nth-child(5) { margin-top: .8rem; }
    }

    @media (max-width: 767.98px) {
        .laporan-kpi-grid,
        .laporan-summary-grid { grid-template-columns: 1fr; }

        .laporan-summary-item,
        .laporan-summary-item:first-child,
        .laporan-summary-item:last-child {
            border-right: 0;
            border-bottom: 1px solid #e9ecef;
            padding: .75rem 0;
            margin-top: 0;
        }

        .laporan-summary-item:last-child { border-bottom: 0; }
    }
</style>

{{-- KPI utama: 5 kartu --}}
<div class="laporan-kpi-grid">

    {{-- Pendapatan Hari Ini --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-success text-uppercase font-weight-bold">Pendapatan Hari Ini</div>
                        <div class="h4 font-weight-bold mb-2 laporan-kpi-value">Rp {{ number_format($pendapatanHariIni,0,',','.') }}</div>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width:100%"></div>
                        </div>
                        <small class="text-muted">Nilai pembayaran hari ini</small>
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
                        <div class="text-xs text-primary text-uppercase font-weight-bold">Pendapatan Bulan Ini</div>
                        <div class="h4 font-weight-bold mb-2 laporan-kpi-value">Rp {{ number_format($pendapatanBulanIni,0,',','.') }}</div>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min($persenLunas,100) }}%"></div>
                        </div>
                        <small class="text-muted">{{ $persenLunas }}% telah dibayar</small>
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
                        <div class="text-xs text-info text-uppercase font-weight-bold">Kas Masuk Bulan Ini</div>
                        <div class="h4 font-weight-bold mb-2 laporan-kpi-value">Rp {{ number_format($kasMasukBulanIni,0,',','.') }}</div>
                        <small class="text-muted d-block">Tagihan Rp {{ number_format($pendapatanBulanIni,0,',','.') }}</small>
                        <small class="text-muted d-block">Admin Rp {{ number_format($biayaAdminBulanIni,0,',','.') }} · Saldo Terbentuk Rp {{ number_format($saldoMasukBulanIni,0,',','.') }}</small>
                    </div>
                    <i class="fas fa-cash-register fa-2x text-info ml-3"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Piutang --}}
    <div class="laporan-kpi-card">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <div class="text-xs text-warning text-uppercase font-weight-bold">Piutang</div>
                        <div class="h4 font-weight-bold mb-2 laporan-kpi-value">Rp {{ number_format($piutang,0,',','.') }}</div>
                        <div class="progress" style="height:5px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min($persenPiutang,100) }}%"></div>
                        </div>
                        <small class="text-muted">{{ $persenPiutang }}% dari total tagihan</small>
                    </div>
                    <i class="fas fa-file-invoice-dollar fa-2x text-warning ml-3"></i>
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
                        <div class="text-xs text-dark text-uppercase font-weight-bold">Total Tagihan</div>
                        <div class="h4 font-weight-bold laporan-kpi-value">Rp {{ number_format($totalTagihan,0,',','.') }}</div>
                        <small class="text-muted">Total tagihan aktif</small>
                    </div>
                    <i class="fas fa-file-invoice fa-2x text-dark ml-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Ringkasan status: 5 item dalam satu panel agar halaman tetap minimalis --}}
<div class="laporan-summary">
    <div class="laporan-summary-title">Ringkasan Tagihan</div>

    <div class="laporan-summary-grid">
        <div class="laporan-summary-item">
            <span class="laporan-summary-icon text-info"><i class="fas fa-users fa-lg"></i></span>
            <div><div class="laporan-summary-value">{{ $pelangganAktif }}</div><div class="laporan-summary-label">Pelanggan Aktif</div></div>
        </div>

        <div class="laporan-summary-item">
            <span class="laporan-summary-icon text-success"><i class="fas fa-check-circle fa-lg"></i></span>
            <div><div class="laporan-summary-value">{{ $totalLunas }}</div><div class="laporan-summary-label">Lunas</div></div>
        </div>

        <div class="laporan-summary-item">
            <span class="laporan-summary-icon text-warning"><i class="fas fa-adjust fa-lg"></i></span>
            <div><div class="laporan-summary-value">{{ $totalSebagian }}</div><div class="laporan-summary-label">Sebagian</div></div>
        </div>

        <div class="laporan-summary-item">
            <span class="laporan-summary-icon text-secondary"><i class="fas fa-clock fa-lg"></i></span>
            <div><div class="laporan-summary-value">{{ $totalBelumBayar }}</div><div class="laporan-summary-label">Belum Bayar</div></div>
        </div>

        <div class="laporan-summary-item">
            <span class="laporan-summary-icon text-danger"><i class="fas fa-exclamation-triangle fa-lg"></i></span>
            <div><div class="laporan-summary-value">{{ $totalJatuhTempo }}</div><div class="laporan-summary-label">Jatuh Tempo</div></div>
        </div>
    </div>
</div>