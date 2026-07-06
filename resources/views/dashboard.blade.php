<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Dashboard GNS</h2>
                <p class="text-sm text-gray-600">Ringkasan cepat data pelanggan, tagihan, dan pembayaran.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @php
                    $cards = [
                        ['Total Pelanggan', $totalPelanggan],
                        ['Pelanggan Aktif', $pelangganAktif],
                        ['Pelanggan Nonaktif', $pelangganNonaktif],
                        ['Router Aktif', $routerAktif],
                        ['Belum Bayar', $tagihanBelumBayar],
                        ['Tagihan Lunas', $tagihanLunas],
                        ['Pendapatan Hari Ini', 'Rp '.number_format($pendapatanHariIni,0,',','.')],
                        ['Pendapatan Bulan Ini', 'Rp '.number_format($pendapatanBulanIni,0,',','.')],
                    ];
                @endphp

                @foreach($cards as $card)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">{{ $card[0] }}</div>
                        <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $card[1] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 font-semibold text-gray-900">5 Pembayaran Terakhir</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left">Invoice</th>
                                    <th class="px-4 py-3 text-left">Pelanggan</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pembayaranTerakhir as $item)
                                    <tr class="border-t border-gray-200">
                                        <td class="px-4 py-3">{{ $item->tagihan->invoice_no }}</td>
                                        <td class="px-4 py-3">{{ $item->tagihan->pelanggan->nama }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($item->total_bayar,0,',','.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada pembayaran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 font-semibold text-gray-900">Tagihan Jatuh Tempo</div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 text-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left">Invoice</th>
                                    <th class="px-4 py-3 text-left">Pelanggan</th>
                                    <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tagihanJatuhTempo as $item)
                                    <tr class="border-t border-gray-200">
                                        <td class="px-4 py-3">{{ $item->invoice_no }}</td>
                                        <td class="px-4 py-3">{{ $item->pelanggan->nama }}</td>
                                        <td class="px-4 py-3">{{ optional($item->tanggal_jatuh_tempo)->format('d-m-Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Tidak ada tagihan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
