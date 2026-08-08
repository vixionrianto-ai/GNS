@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Pelanggan</h4>
            <p class="text-muted small mb-0">Kelola seluruh pelanggan GNS Network, sinkronisasi MikroTik, dan status layanan pelanggan.</p>
        </div>
    </div>

    <!-- Statistik Cards Bootstrap -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Pelanggan</span>
                        <h3 class="mb-0 fw-bold text-dark mt-1">{{ $totalPelanggan ?? 80 }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                        <i class="fas fa-users fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Pelanggan Aktif</span>
                        <h3 class="mb-0 fw-bold text-success mt-1">{{ $pelangganAktif ?? 79 }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-3">
                        <i class="fas fa-user-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Non Aktif</span>
                        <h3 class="mb-0 fw-bold text-danger mt-1">{{ $pelangganNonAktif ?? 1 }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3">
                        <i class="fas fa-user-slash fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Paket Internet</span>
                        <h3 class="mb-0 fw-bold text-warning mt-1">{{ $totalPaket ?? 4 }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3">
                        <i class="fas fa-wifi fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Utama & Filter / Action Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-table text-primary me-2"></i> Daftar Pelanggan</h5>
                <p class="text-muted small mb-0">Daftar seluruh pelanggan yang terhubung dengan sistem GNS Network.</p>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('pelanggan.create') }}" class="btn btn-sm btn-primary px-3 rounded-pill fw-semibold"><i class="fas fa-user-plus me-1"></i> Tambah Pelanggan</a>
                <button type="button" class="btn btn-sm btn-success px-3 rounded-pill fw-semibold"><i class="fas fa-sync me-1"></i> Sinkron MikroTik</button>
                <a href="{{ route('pelanggan.index') }}" class="btn btn-sm btn-light border px-3 rounded-pill fw-semibold"><i class="fas fa-sync-alt me-1"></i> Refresh</a>
            </div>
        </div>

        <!-- Form Filter & Pencarian -->
        <div class="card-body bg-light border-top border-bottom py-3">
            <form method="GET" action="{{ route('pelanggan.index') }}" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm shadow-none">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-3 shadow-none" placeholder="Cari nama pelanggan, kode, atau no HP...">
                        <button type="submit" class="btn btn-primary px-3 rounded-end-3"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Data Pelanggan -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-secondary">
                    <tr>
                        <th class="ps-4 py-3">No</th>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>No. HP</th>
                        <th>Router</th>
                        <th>Paket</th>
                        <th>Username PPPoE</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($pelanggans)
                        @forelse($pelanggans as $index => $item)
                        <tr>
                            <td class="ps-4 fw-semibold text-secondary">{{ method_exists($pelanggans, 'firstItem') ? $pelanggans->firstItem() + $index : $index + 1 }}</td>
                            <td><span class="fw-bold text-primary">{{ $item->kode_pelanggan ?? '-' }}</span></td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->nama ?? '-' }}</div>
                                <small class="text-muted">ID: {{ $item->kode_pelanggan ?? '-' }}</small>
                            </td>
                            <td>{{ $item->no_hp ?? '-' }}</td>
                            <td>{{ optional($item->router)->nama ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border px-2 py-1">{{ optional($item->paket)->nama_paket ?? '-' }}</span></td>
                            <td><code>{{ $item->username_pppoe ?? '-' }}</code></td>
                            <td>
                                @php $status = strtolower(trim($item->status ?? '')); @endphp
                                @if($status == 'aktif' || $status == 'active')
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill">Aktif</span>
                                @else
                                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill">Non Aktif</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    <a href="{{ route('pelanggan.show', $item->id) }}" class="btn btn-sm btn-light text-primary border-0" title="Detail Pelanggan">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pelanggan.edit', $item->id) }}" class="btn btn-sm btn-light text-warning border-0" title="Edit Pelanggan">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('pelanggan.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data pelanggan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger border-0" title="Hapus Pelanggan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="py-4">
                                    <div class="text-primary bg-primary bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                        <i class="fas fa-users-slash fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Pelanggan Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-0">Belum ada data pelanggan yang terdaftar atau sesuai dengan pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="py-4">
                                    <div class="text-primary bg-primary bg-opacity-10 d-inline-flex p-4 rounded-circle mb-3">
                                        <i class="fas fa-users-slash fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Data Pelanggan Tidak Ditemukan</h6>
                                    <p class="text-muted small mb-0">Silakan tambahkan data pelanggan baru melalui tombol di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endisset
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination -->
        <div class="card-footer bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <span class="text-muted small">Menampilkan data pelanggan sistem</span>
            @if(isset($pelanggans) && method_exists($pelanggans, 'links'))
                <div>{{ $pelanggans->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection