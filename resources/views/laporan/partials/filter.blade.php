<div class="card shadow-sm mb-4 laporan-filter-card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h5 class="mb-0 font-weight-bold">
            <i class="fas fa-filter text-primary mr-2"></i>
            Filter Laporan
        </h5>
        <small class="text-muted d-none d-md-inline">Gunakan filter untuk menyaring data laporan</small>
    </div>

    <div class="card-body py-3">
        <form method="GET" action="{{ route('laporan.index') }}">
            <div class="row align-items-end">
                <div class="col-xl-2 col-lg-3 col-md-6 mb-3 mb-xl-0">
                    <label class="small font-weight-bold text-muted">Dari Tanggal</label>
                    <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                           value="{{ request('tanggal_awal') }}">
                </div>

                <div class="col-xl-2 col-lg-3 col-md-6 mb-3 mb-xl-0">
                    <label class="small font-weight-bold text-muted">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                           value="{{ request('tanggal_akhir') }}">
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 mb-3 mb-xl-0">
                    <label class="small font-weight-bold text-muted">Bulan</label>
                    <select class="form-control form-control-sm" name="bulan">
                        <option value="">Semua</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @selected(request('bulan') == $i)>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 mb-3 mb-xl-0">
                    <label class="small font-weight-bold text-muted">Tahun</label>
                    <select class="form-control form-control-sm" name="tahun">
                        <option value="">Semua</option>
                        @for($i = date('Y'); $i >= 2024; $i--)
                            <option value="{{ $i }}" @selected(request('tahun') == $i)>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-4 mb-3 mb-xl-0">
                    <label class="small font-weight-bold text-muted">Status</label>
                    <select class="form-control form-control-sm" name="status">
                        <option value="">Semua</option>
                        <option value="Lunas" @selected(request('status') == 'Lunas')>Lunas</option>
                        <option value="Sebagian" @selected(request('status') == 'Sebagian')>Sebagian</option>
                        <option value="Belum Bayar" @selected(request('status') == 'Belum Bayar')>Belum Bayar</option>
                        <option value="Jatuh Tempo" @selected(request('status') == 'Jatuh Tempo')>Jatuh Tempo</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-4 col-md-8 mb-3 mb-xl-0">
                    <label class="small font-weight-bold text-muted">Cari Pelanggan / Invoice</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control"
                               placeholder="Nama, kode, invoice..."
                               value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" title="Terapkan filter">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-2">
                <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-light border mr-2">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-filter mr-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>
