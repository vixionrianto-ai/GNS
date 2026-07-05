<x-app-layout>

    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">

            Data Tagihan GNS

        </h2>

    </x-slot>

    <div class="py-6">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-alert-success />

            <x-alert-error />

            <div class="flex items-center gap-3 mb-4">

                <form
                    action="{{ route('tagihan.generate') }}"
                    method="POST">

                    @csrf

                    <button
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow">

                        ⚡ Generate Tagihan

                    </button>

                </form>

            </div>

            <div class="bg-white shadow rounded-lg p-4 mb-4">

                <form
                    method="GET"
                    action="{{ route('tagihan.index') }}">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <div>

                            <label class="block text-sm font-medium mb-1">

                                Periode

                            </label>

                            <input
                                type="month"
                                name="periode"
                                value="{{ request('periode') }}"
                                class="w-full border rounded px-3 py-2">

                        </div>

                        <div>

                            <label class="block text-sm font-medium mb-1">

                                Status

                            </label>

                            <select
                                name="status"
                                class="w-full border rounded px-3 py-2">

                                <option value="">Semua</option>

                                <option
                                    value="Belum Bayar"
                                    {{ request('status')=='Belum Bayar' ? 'selected' : '' }}>

                                    Belum Bayar

                                </option>

                                <option
                                    value="Lunas"
                                    {{ request('status')=='Lunas' ? 'selected' : '' }}>

                                    Lunas

                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="block text-sm font-medium mb-1">

                                Cari Pelanggan

                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nama pelanggan..."
                                class="w-full border rounded px-3 py-2">

                        </div>

                        <div class="flex items-end">

                            <button
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">

                                🔍 Filter

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            <div class="bg-white shadow rounded-lg p-4">

                <table class="table-auto w-full border">

                    <thead>

                        <tr class="bg-gray-200">

                            <th class="border p-2">No</th>

                            <th class="border p-2">Invoice</th>

                            <th class="border p-2">Pelanggan</th>

                            <th class="border p-2">Paket</th>

                            <th class="border p-2">Periode</th>

                            <th class="border p-2">Total</th>

                            <th class="border p-2">Status</th>

                            <th class="border p-2">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($tagihans as $tagihan)
                                                <tr>

                            <td class="border p-2 text-center">

                                {{ $tagihans->firstItem() + $loop->index }}

                            </td>

                            <td class="border p-2">

                                {{ $tagihan->invoice_no }}

                            </td>

                            <td class="border p-2">

                                {{ $tagihan->pelanggan->nama }}

                                <br>

                                <small class="text-gray-500">

                                    {{ $tagihan->pelanggan->no_hp }}

                                </small>

                            </td>

                            <td class="border p-2">

                                {{ $tagihan->pelanggan->paket->nama_paket ?? '-' }}

                            </td>

                            <td class="border p-2 text-center">

                                {{ $tagihan->periode }}

                            </td>

                            <td class="border p-2 text-right">

                                Rp {{ number_format($tagihan->total,0,',','.') }}

                            </td>

                            <td class="border p-2 text-center">

                                @if($tagihan->status == 'Lunas')

                                    <span
                                        class="bg-green-500 text-white px-3 py-1 rounded">

                                        Lunas

                                    </span>

                                @else

                                    <span
                                        class="bg-red-500 text-white px-3 py-1 rounded">

                                        Belum Bayar

                                    </span>

                                @endif

                            </td>

                            <td class="border p-2">

                                <div class="flex gap-2 justify-center">

                                    <a
                                        href="{{ route('tagihan.show',$tagihan->id) }}"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded">

                                        👁 Detail

                                    </a>

                                    @if($tagihan->status != 'Lunas')

                                    <form
                                        action="{{ route('tagihan.destroy',$tagihan->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus tagihan ini?')">

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded">

                                            🗑 Hapus

                                        </button>

                                    </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="8"
                                class="text-center p-5 text-gray-500">

                                Belum ada data tagihan.

                            </td>

                        </tr>

                        @endforelse
                                            </tbody>

                </table>

                <div class="mt-4">

                    {{ $tagihans->withQueryString()->links() }}

                </div>

            </div>

        </div>

    </div>

</x-app-layout>