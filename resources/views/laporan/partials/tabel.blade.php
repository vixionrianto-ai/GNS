{{-- ================= DATA LAPORAN ================= --}}

<div class="card shadow-sm mt-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="fas fa-table text-primary"></i>
            Data Laporan Pembayaran
        </h5>

        <div>

            <button class="btn btn-sm btn-outline-secondary">

                <i class="fas fa-print"></i>

                Print

            </button>

            <button class="btn btn-sm btn-danger">

                <i class="fas fa-file-pdf"></i>

                PDF

            </button>

            <button class="btn btn-sm btn-success">

                <i class="fas fa-file-excel"></i>

                Excel

            </button>

        </div>

    </div>

    <div class="card-body">

        <form
    method="GET"
    action="{{ route('laporan.index') }}">

    <div class="row mb-3">

        <div class="col-md-4">

            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Cari pelanggan / invoice ..."
                value="{{ request('search') }}">

        </div>

        <div class="col-md-2">

            <button
                class="btn btn-primary">

                <i class="fas fa-search"></i>

                Cari

            </button>

        </div>

    </div>

</form>

        <div class="table-responsive">

<table class="table table-hover table-bordered align-middle">

    <thead class="thead-light">

        <tr>

            <th width="60">No</th>

            <th>Tanggal Tagihan</th>
            <th>Invoice</th>
            <th>Pelanggan</th>
            <th>Paket</th>
            <th>Periode</th>
            <th class="text-end">Total</th>
            <th class="text-end">Dibayar</th>
            <th class="text-end">Sisa</th>
            <th>Status</th>
            <th>Jatuh Tempo</th>
            <th width="90">Aksi</th>

        </tr>

    </thead>

    <tbody>

        @forelse($laporan as $item)

        <tr>

            <td>
                {{ $loop->iteration + (($laporan->currentPage()-1) * $laporan->perPage()) }}
            </td>

            <td>
                {{ optional($item->tanggal_tagihan)->format('d-m-Y') }}
            </td>

            <td>
                <strong>{{ $item->invoice_no }}</strong>
            </td>

            <td>
                {{ optional($item->pelanggan)->nama }}
            </td>

            <td>
                {{ optional(optional($item->pelanggan)->paket)->nama ?? '-' }}
            </td>

            <td>
                {{ $item->periode }}
            </td>

            <td class="text-end">
                Rp {{ number_format($item->total,0,',','.') }}
            </td>

            <td class="text-end">
                Rp {{ number_format($item->dibayar,0,',','.') }}
            </td>

            <td class="text-end">
                Rp {{ number_format($item->sisa,0,',','.') }}
            </td>

            <td>

                @switch($item->status)

                    @case('Lunas')
                        <span class="badge badge-success">Lunas</span>
                        @break

                    @case('Sebagian')
                        <span class="badge badge-warning">Sebagian</span>
                        @break

                    @case('Jatuh Tempo')
                        <span class="badge badge-danger">Jatuh Tempo</span>
                        @break

                    @default
                        <span class="badge badge-secondary">
                            {{ $item->status }}
                        </span>

                @endswitch

            </td>

            <td>

                {{ optional($item->tanggal_jatuh_tempo)->format('d-m-Y') }}

            </td>

            <td>

                <a
                    href="{{ route('tagihan.show',$item) }}"
                    class="btn btn-sm btn-primary">

                    <i class="fas fa-eye"></i>

                </a>

            </td>

        </tr>

        @empty

        <tr>

            <td colspan="11" class="text-center text-muted py-5">

                <i class="fas fa-folder-open fa-3x mb-3"></i>

                <br>

                Tidak ada data tagihan.

            </td>

        </tr>

        @endforelse

    </tbody>

</table>

</div>

<div class="mt-3">

    {{ $laporan->links() }}

</div>

</div>
    </div>

</div>