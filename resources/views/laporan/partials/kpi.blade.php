<div class="row">

    {{-- Pendapatan Hari Ini --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-success text-uppercase font-weight-bold">
                            Pendapatan Hari Ini
                        </div>
                        <div class="h4 font-weight-bold">
                            Rp {{ number_format($pendapatanHariIni,0,',','.') }}
                        </div>
                        <div class="progress mt-3" style="height:6px;">
                            <div class="progress-bar bg-dark" role="progressbar" style="width:100%"></div>
                        </div>
                        <small class="text-muted">Nilai pembayaran tagihan hari ini</small>
                    </div>
                    <i class="fas fa-wallet fa-2x text-success"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Pendapatan Bulan --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-primary text-uppercase font-weight-bold">
                            Pendapatan Bulan
                        </div>
                        <div class="h4 font-weight-bold">
                            Rp {{ number_format($pendapatanBulanIni,0,',','.') }}
                        </div>
                        <div class="progress mt-3" style="height:6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $persenLunas }}%"></div>
                        </div>
                        <small class="text-muted">{{ $persenLunas }}% dari total tagihan telah dibayar</small>
                    </div>
                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Kas Masuk --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-info text-uppercase font-weight-bold">
                            Kas Masuk Bulan
                        </div>
                        <div class="h4 font-weight-bold">
                            Rp {{ number_format($kasMasukBulanIni,0,',','.') }}
                        </div>
                        <small class="text-muted d-block">
                            Tagihan Rp {{ number_format($pendapatanBulanIni,0,',','.') }}
                        </small>
                        <small class="text-muted d-block">
                            Admin Rp {{ number_format($biayaAdminBulanIni,0,',','.') }}
                            · Saldo Rp {{ number_format($saldoMasukBulanIni,0,',','.') }}
                        </small>
                    </div>
                    <i class="fas fa-cash-register fa-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Tagihan --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-dark shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-dark text-uppercase font-weight-bold">
                            Total Tagihan
                        </div>
                        <div class="h4 font-weight-bold">
                            Rp {{ number_format($totalTagihan,0,',','.') }}
                        </div>
                    </div>
                    <i class="fas fa-file-invoice fa-2x text-dark"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Piutang --}}
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-warning text-uppercase font-weight-bold">Piutang</div>
                        <div class="h4 font-weight-bold">Rp {{ number_format($piutang,0,',','.') }}</div>
                        <div class="progress mt-3" style="height:6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $persenPiutang }}%"></div>
                        </div>
                        <small class="text-muted">{{ $persenPiutang }}% dari total tagihan</small>
                    </div>
                    <i class="fas fa-file-invoice-dollar fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Pelanggan Aktif --}}
    <div class="col-xl-3 col-md-3 mb-4">
        <div class="card border-left-info shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-2x text-info mb-2"></i>
                <h3>{{ $pelangganAktif }}</h3>
                <small>Pelanggan Aktif</small>
            </div>
        </div>
    </div>

    {{-- Lunas --}}
    <div class="col-xl-3 col-md-3 mb-4">
        <div class="card border-left-success shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h3>{{ $totalLunas }}</h3>
                <small>Lunas</small>
            </div>
        </div>
    </div>

    {{-- Sebagian --}}
    <div class="col-xl-2 col-md-2 mb-4">
        <div class="card border-left-warning shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-adjust fa-2x text-warning mb-2"></i>
                <h3>{{ $totalSebagian }}</h3>
                <small>Sebagian</small>
            </div>
        </div>
    </div>

    {{-- Belum Bayar --}}
    <div class="col-xl-2 col-md-2 mb-4">
        <div class="card border-left-secondary shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x text-secondary mb-2"></i>
                <h3>{{ $totalBelumBayar }}</h3>
                <small>Belum Bayar</small>
            </div>
        </div>
    </div>

    {{-- Jatuh Tempo --}}
    <div class="col-xl-2 col-md-2 mb-4">
        <div class="card border-left-danger shadow h-100">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h3>{{ $totalJatuhTempo }}</h3>
                <small>Jatuh Tempo</small>
            </div>
        </div>
    </div>

</div>