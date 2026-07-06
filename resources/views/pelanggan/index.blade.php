<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Data Pelanggan GNS</h2>
                <p class="text-sm text-gray-600">Kelola data pelanggan dan sinkronisasi MikroTik dengan lebih rapi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-3 d-flex gap-2">

                <a href="{{ route('pelanggan.create') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    ➕ Tambah Pelanggan
                </a>

                <form action="{{ route('pelanggan.sync') }}" method="POST" class="d-inline">
                    @csrf

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-sync-alt"></i>
                        🔄 Sinkron MikroTik
                    </button>
                </form>

            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Kode</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-left">No HP</th>
                                <th class="px-4 py-3 text-left">Paket</th>
                                <th class="px-4 py-3 text-left">Username PPPoE</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pelanggans as $pelanggan)
                                <tr class="border-t border-gray-200">
                                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $pelanggan->kode_pelanggan }}</td>
                                    <td class="px-4 py-3">{{ $pelanggan->nama }}</td>
                                    <td class="px-4 py-3">{{ $pelanggan->no_hp }}</td>
                                    <td class="px-4 py-3">{{ $pelanggan->paket?->nama_paket ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $pelanggan->username_pppoe ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($pelanggan->status == 'Aktif')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">Aktif</span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">Non Aktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('pelanggan.edit', $pelanggan->id) }}" class="rounded-lg bg-yellow-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600">
                                                ✏ Edit
                                            </a>

                                            <form action="{{ route('pelanggan.destroy', $pelanggan->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                                    🗑 Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada data pelanggan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>