<x-app-layout>

    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">

            Detail Tagihan

        </h2>

    </x-slot>

    <div class="py-6">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <x-alert-success />

            <x-alert-error />

            <div class="bg-white shadow rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">

                    <h3 class="text-xl font-bold">

                        {{ $tagihan->invoice_no }}

                    </h3>

                    <a href="{{ route('tagihan.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">

                        ← Kembali

                    </a>

                </div>

                <table class="table-auto w-full">

                    <tbody>

                        <tr>

                            <td class="py-2 font-semibold w-64">
                                Nama Pelanggan
                            </td>

                            <td>
                                {{ $tagihan->pelanggan->nama }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                No HP
                            </td>

                            <td>
                                {{ $tagihan->pelanggan->no_hp }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Router
                            </td>

                            <td>
                                {{ $tagihan->pelanggan->router->nama ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Paket
                            </td>

                            <td>
                                {{ $tagihan->pelanggan->paket->nama_paket ?? '-' }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Periode
                            </td>

                            <td>
                                {{ $tagihan->periode }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Tanggal Tagihan
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($tagihan->tanggal_tagihan)->format('d-m-Y') }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Jatuh Tempo
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d-m-Y') }}
                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Nominal
                            </td>

                            <td>

                                Rp {{ number_format($tagihan->nominal,0,',','.') }}

                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Denda
                            </td>

                            <td>

                                Rp {{ number_format($tagihan->denda,0,',','.') }}

                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Total
                            </td>

                            <td class="font-bold text-lg text-blue-700">

                                Rp {{ number_format($tagihan->total,0,',','.') }}

                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Status
                            </td>

                            <td>

                                @if($tagihan->status=='Lunas')

                                    <span class="bg-green-500 text-white px-3 py-1 rounded">

                                        Lunas

                                    </span>

                                @else

                                    <span class="bg-red-500 text-white px-3 py-1 rounded">

                                        Belum Bayar

                                    </span>

                                @endif

                            </td>

                        </tr>

                        <tr>

                            <td class="py-2 font-semibold">
                                Keterangan
                            </td>

                            <td>

                                {{ $tagihan->keterangan }}

                            </td>

                        </tr>
                        <tr>

                            <td class="py-2 font-semibold">
                                Tanggal Bayar
                            </td>

                            <td>

                                @if($tagihan->tanggal_bayar)

                                    {{ \Carbon\Carbon::parse($tagihan->tanggal_bayar)->format('d-m-Y H:i') }}

                                @else

                                    -

                                @endif

                            </td>

                        </tr>
                    </tbody>

                </table>
                <div class="mt-8 flex gap-3">

                    @if($tagihan->status != \App\Models\Tagihan::STATUS_LUNAS)

                        <a href="#"

                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">

                            💰 Bayar Tagihan

                        </a>

                    @else

                        <a href="#"

                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">

                            🖨 Cetak Invoice

                        </a>

                    @endif

                </div>
            </div>

        </div>

    </div>

</x-app-layout>