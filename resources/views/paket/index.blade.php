<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Paket Internet
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Tombol --}}
            <div class="mb-4 flex justify-between">

                <a href="{{ route('paket.create') }}"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">

                    ➕ Tambah Paket

                </a>

            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">

                <table class="min-w-full">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="px-4 py-3 border">No</th>
                            <th class="px-4 py-3 border">Nama Paket</th>
                            <th class="px-4 py-3 border">Kecepatan</th>
                            <th class="px-4 py-3 border text-right">Harga</th>
                            <th class="px-4 py-3 border text-center">Status</th>
                            <th class="px-4 py-3 border text-center">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($pakets as $paket)

                        <tr class="hover:bg-gray-50">

                            <td class="border px-4 py-3 text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td class="border px-4 py-3">
                                {{ $paket->nama_paket }}
                            </td>

                            <td class="border px-4 py-3">
                                {{ $paket->kecepatan }}
                            </td>

                            <td class="border px-4 py-3 text-right">
                                Rp {{ number_format($paket->harga,0,',','.') }}
                            </td>

                            <td class="border px-4 py-3 text-center">

                                @if($paket->status=='Aktif')

                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">

                                        Aktif

                                    </span>

                                @else

                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">

                                        Nonaktif

                                    </span>

                                @endif

                            </td>

                            <td class="border px-4 py-3">

                                <div class="flex justify-center gap-2">

                                    <a href="{{ route('paket.edit',$paket->id) }}"
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded">

                                        ✏ Edit

                                    </a>

                                    <form action="{{ route('paket.destroy',$paket->id) }}"
                                          method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Yakin ingin menghapus paket ini?')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded">

                                            🗑 Hapus

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="6"
                                class="text-center py-6 text-gray-500">

                                Belum ada paket internet.

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</x-app-layout>