<div class="card shadow-sm mt-4 laporan-table-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h5 class="mb-1 font-weight-bold">
                <i class="fas fa-table text-primary mr-2"></i>
                Data Laporan Tagihan
            </h5>
            <small class="text-muted">Data mengikuti filter yang sedang aktif</small>
        </div>

        <div class="mt-2 mt-md-0 d-flex align-items-center">
            <span class="badge badge-light border mr-2 px-2 py-1">
                {{ number_format($laporan->total(), 0, ',', '.') }} Data
            </span>
            <a href="{{ route('laporan.export.pdf', request()->query()) }}"
               class="btn btn-sm btn-danger mr-1" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            <a href="{{ route('laporan.export.excel', request()->query()) }}"
               class="btn btn-sm btn-success">
                <i class="fas fa-file-excel mr-1"></i> Excel
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 laporan-table">
                <thead>
                    <tr>
                        <th width="55">No</th>
                        <th>Invoice</th>
                        <th>Pelanggan</th>
                        <th>Periode</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Dibayar</th>
                        <th class="text-right">Sisa</th>
                        <th>Status</th>
                        <th width="60" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $item)
                        <tr>
                            <td class="text-muted">
                                {{ $loop->iteration + (($laporan->currentPage() - 1) * $laporan->perPage()) }}
                            </td>
                            <td>
                                <strong class="d-block">{{ $item->invoice_no }}</strong>
                                <small class="text-muted">
                                    {{ optional($item->tanggal_tagihan)->format('d-m-Y') }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ optional($item->pelanggan)->nama }}</strong>
                                <small class="text-muted d-block">
                                    {{ optional(optional($item->pelanggan)->paket)->nama ?? '-' }}
                                </small>
                            </td>
                            <td>{{ $item->periode }}</td>
                            <td class="text-right font-weight-bold">
                                Rp {{ number_format($item->total, 0, ',', '.') }}
                            </td>
                            <td class="text-right text-success">
                                Rp {{ number_format($item->dibayar, 0, ',', '.') }}
                            </td>
                            <td class="text-right {{ $item->sisa > 0 ? 'text-danger' : 'text-muted' }}">
                                Rp {{ number_format($item->sisa, 0, ',', '.') }}
                            </td>
                            <td>
                                @switch($item->status)
                                    @case('Lunas')
                                        <span class="badge badge-success px-2 py-1">Lunas</span>
                                        @break
                                    @case('Sebagian')
                                        <span class="badge badge-warning px-2 py-1">Sebagian</span>
                                        @break
                                    @case('Jatuh Tempo')
                                        <span class="badge badge-danger px-2 py-1">Jatuh Tempo</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary px-2 py-1">{{ $item->status }}</span>
                                @endswitch
                            </td>
                            <td class="text-center">
                                <a href="{{ route('tagihan.show', $item) }}"
                                   class="btn btn-sm btn-light border"
                                   title="Lihat tagihan">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <div>Tidak ada data tagihan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3 py-3">
            {{ $laporan->links() }}
        </div>
    </div>
</div>
