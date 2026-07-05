<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Pelanggan GNS
        </h2>
    </x-slot>

    {{-- Alert Success --}}
    @if(session('success'))

        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">

            {{ session('success') }}

        </div>

    @endif

    {{-- Alert Error --}}
    @if($errors->any())

        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">

            {{ $errors->first() }}

        </div>

    @endif    
    <div class="flex items-center gap-3 mb-4">
   
    <a href="{{ route('pelanggan.create') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow">

        ➕ Tambah Pelanggan

    </a>

    <form action="{{ route('pelanggan.sync') }}"
          method="POST">

        @csrf

        <button
            class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2 rounded-lg shadow">

            🔄 Sinkron MikroTik

        </button>

    </form>

</div>
            <div class="bg-white shadow rounded mt-4 p-4">

                <table class="table-auto w-full border">
                    <thead>                       
                        <tr class="bg-gray-200">
                            <th class="border p-2">No</th>
                            <th class="border p-2">Kode</th>
                            <th class="border p-2">Nama</th>
                            <th class="border p-2">No HP</th>
                            <th class="border p-2">Paket</th>
                            <th class="border p-2">Username PPPoE</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pelanggans as $pelanggan)
                        <tr>
                            <td class="border p-2">{{ $loop->iteration }}</td>

                            <td class="border p-2">
                                {{ $pelanggan->kode_pelanggan }}
                            </td>

                            <td class="border p-2">
                                {{ $pelanggan->nama }}
                            </td>

                            <td class="border p-2">
                                {{ $pelanggan->no_hp }}
                            </td>

                            <td class="border p-2">
                                {{ $pelanggan->paket?->nama_paket ?? '-' }}
                            </td>

                            <td class="border p-2">
                                {{ $pelanggan->username_pppoe ?? '-' }}
                            </td>

                            <td class="border p-2">
                                @if($pelanggan->status == 'Aktif')

                                <span class="bg-green-500 text-white px-2 py-1 rounded">
                                    Aktif
                                </span>

                                @else

                                <span class="bg-red-500 text-white px-2 py-1 rounded">
                                    Non Aktif
                                </span>

                                @endif
                            </td>
                        
                        
                        <td class="px-4 py-2">

            <div class="flex items-center gap-2">

                <a href="{{ route('pelanggan.edit',$pelanggan->id) }}"
                class="bg-yellow-500 hover:bg-yellow-600
                        text-white px-4 py-2 rounded">

                    ✏ Edit

                </a>

                <form action="{{ route('pelanggan.destroy',$pelanggan->id) }}"
                    method="POST"
                    onsubmit="return confirm('Yakin ingin menghapus pelanggan ini?')">

                    @csrf
                    @method('DELETE')

                    <button
                        class="bg-red-600 hover:bg-red-700
                            text-white px-4 py-2 rounded">

                        🗑 Hapus

                    </button>

                </form>

            </div>

        </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center p-3">
                                Belum ada data pelanggan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

        </div>
    </div>
</x-app-layout>