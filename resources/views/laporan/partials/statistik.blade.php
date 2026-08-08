<div class="col-lg-4">

        <div class="card shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-primary"></i>
                    Statistik
                </h5>

            </div>

            <div class="card-body">

                <div class="mb-3">

                    <strong>Pelanggan Aktif</strong>

                    <div class="progress mt-2">

                        <div
                            class="progress-bar bg-success"
                            style="width:100%">

                            {{ $pelangganAktif }}

                        </div>

                    </div>

                </div>

                <div class="mb-3">

                    <strong>Total Piutang</strong>

                    <h4 class="text-warning">

                        Rp {{ number_format($piutang,0,',','.') }}

                    </h4>

                </div>

                <div>

                    <strong>Pendapatan Bulan Ini</strong>

                    <h4 class="text-primary">

                        Rp {{ number_format($pendapatanBulanIni,0,',','.') }}

                    </h4>

                </div>

            </div>

        </div>

    </div>
    <div class="col-lg-8">

    <div class="card shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                <i class="fas fa-wallet text-danger"></i>
                Top 10 Piutang
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>

                        <tr>
                            <th width="60">#</th>
                            <th>Pelanggan</th>
                            <th class="text-right">Sisa Tagihan</th>
                        </tr>

                    </thead>

                    <tbody>

                    @forelse($topPiutang as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ optional($item->pelanggan)->nama ?? '-' }}
                            </td>

                            <td class="text-right text-danger font-weight-bold">
                                Rp {{ number_format($item->sisa,0,',','.') }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="3" class="text-center text-muted">
                                Tidak ada piutang.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>