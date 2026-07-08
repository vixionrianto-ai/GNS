<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Invoice Pembayaran
        </h2>
    </x-slot>

    <div class="container mt-4">

        <div class="card shadow">

            <div class="card-header bg-success text-white">

                <h4 class="mb-0">
                    INVOICE PEMBAYARAN
                </h4>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="250">Nomor Invoice</th>
                        <td>{{ $pembayaran->invoice_no }}</td>
                    </tr>

                    <tr>
                        <th>Tanggal Invoice</th>
                        <td>{{ $pembayaran->invoice_date }}</td>
                    </tr>

                    <tr>
                        <th>Nama Pelanggan</th>
                        <td>{{ $pembayaran->tagihan->pelanggan->nama }}</td>
                    </tr>

                    <tr>
                        <th>Paket</th>
                        <td>{{ $pembayaran->tagihan->pelanggan->paket->nama_paket }}</td>
                    </tr>

                    <tr>
                        <th>Periode</th>
                        <td>{{ $pembayaran->tagihan->periode }}</td>
                    </tr>

                    <tr>
                        <th>Metode Pembayaran</th>
                        <td>{{ $pembayaran->metode }}</td>
                    </tr>

                    <tr>
                        <th>Total Bayar</th>
                        <td>
                            Rp
                            {{ number_format($pembayaran->total_bayar,0,',','.') }}
                        </td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>

                            @if($pembayaran->status=='Berhasil')

                                <span class="badge bg-success">
                                    LUNAS
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    BELUM LUNAS
                                </span>

                            @endif

                        </td>
                    </tr>

                </table>

            </div>

            <div class="card-footer">

                <a
                    href="{{ route('tagihan.index') }}"
                    class="btn btn-secondary">

                    Kembali

                </a>

                <a
                    href="{{ route('pembayaran.pdf',$pembayaran) }}"
                    class="btn btn-danger">

                    Download PDF

                </a>

            </div>

        </div>

    </div>

</x-app-layout>