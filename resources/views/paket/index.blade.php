<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Data Paket Internet</h2>
                <p class="text-sm text-gray-600">Kelola paket internet dan status aktif/nonaktif.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('paket.create') }}" class="btn btn-success fw-bold">
                <i class="fas fa-plus"></i> 
                ➕ Tambah Paket
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama Paket</th>
                                <th class="px-4 py-3 text-left">Kecepatan</th>
                                <th class="px-4 py-3 text-right">Harga</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pakets as $paket)
                                <tr class="border-t border-gray-200">
                                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $paket->nama_paket }}</td>
                                    <td class="px-4 py-3">{{ $paket->kecepatan }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($paket->harga,0,',','.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($paket->status == 'Aktif')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Aktif</span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('paket.edit', $paket->id) }}" class="rounded-lg bg-yellow-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600">
                                                ✏ Edit
                                            </a>

                                            <form action="{{ route('paket.destroy', $paket->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Yakin ingin menghapus paket ini?')" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                                    🗑 Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada paket internet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>