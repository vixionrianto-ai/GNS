<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Data Tagihan GNS</h2>
                <p class="text-sm text-gray-600">Kelola tagihan pelanggan dengan tampilan yang lebih rapi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-alert-success />
            <x-alert-error />

            <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <form action="{{ route('tagihan.generate') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-green-700">
                        ⚡ Generate Tagihan
                    </button>
                </form>
            </div>

            <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('tagihan.index') }}">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Periode</label>
                            <input type="month" name="periode" value="{{ request('periode') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua</option>
                                <option value="Belum Bayar" {{ request('status') == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Cari Pelanggan</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama pelanggan..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex items-end">
                            <button class="w-full rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white transition hover:bg-blue-700">
                                🔍 Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Invoice</th>
                                <th class="px-4 py-3 text-left">Pelanggan</th>
                                <th class="px-4 py-3 text-left">Paket</th>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tagihans as $tagihan)
                                <tr class="border-t border-gray-200">
                                    <td class="px-4 py-3 text-center">{{ $tagihans->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $tagihan->invoice_no }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $tagihan->pelanggan->nama }}</div>
                                        <div class="text-xs text-gray-500">{{ $tagihan->pelanggan->no_hp }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $tagihan->pelanggan->paket->nama_paket ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $tagihan->periode }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($tagihan->total,0,',','.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($tagihan->status == 'Lunas')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Lunas</span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('tagihan.show', $tagihan->id) }}" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                                👁 Detail
                                            </a>

                                            @if($tagihan->status != 'Lunas')
                                                <form action="{{ route('tagihan.destroy', $tagihan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tagihan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                                        🗑 Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada data tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $tagihans->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>