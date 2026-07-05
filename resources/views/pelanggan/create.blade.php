<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Pelanggan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('pelanggan.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block font-semibold">Nama</label>
                        <input type="text"
                               name="nama"
                               class="border rounded w-full p-2"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Alamat</label>
                        <textarea name="alamat"
                                  class="border rounded w-full p-2"
                                  required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">No HP</label>
                        <input type="text"
                               name="no_hp"
                               class="border rounded w-full p-2"
                               required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Paket Internet</label>
                        <select name="paket_id" class="border rounded w-full p-2" required>
                        <option value="">-- Pilih Paket --</option>
                        @foreach($pakets as $paket)
                        <option value="{{ $paket->id }}">
                        {{ $paket->nama_paket }}
                        ({{ $paket->kecepatan }})
                        - Rp {{ number_format($paket->harga,0,',','.') }}
                        </option>
                        @endforeach
                        </select>
                        </div>                   
                    <div class="mb-4">
                        <label class="block font-semibold">Router MikroTik</label>
                        <select name="router_id"
                                class="border rounded w-full p-2"
                                required>
                            <option value="">-- Pilih Router --</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}">
                                    {{ $router->nama_router }}
                                    ({{ $router->ip_router }})
                                </option>

                            @endforeach

                        </select>
                    </div>


                    
                    <div class="mb-4">
                        <label class="block font-semibold">Username PPPoE</label>
                        <input type="text"
                            name="username_pppoe"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Password PPPoE</label>
                        <input type="text"
                            name="password_pppoe"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">IP Address</label>
                        <input type="text"
                            name="ip_address"
                            class="border rounded w-full p-2"
                            placeholder="Kosongkan jika DHCP">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">MAC Address</label>
                        <input type="text"
                            name="mac_address"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Tanggal Pasang</label>
                        <input type="date"
                            name="tanggal_pasang"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Tanggal Aktif</label>
                        <input type="date"
                            name="tanggal_aktif"
                            class="border rounded w-full p-2">
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold">Status</label>
                        <select name="status" class="border rounded w-full p-2">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <div class="mt-4">

                        <button type="submit"
                        style="background:#16a34a;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">
                        Simpan
                        </button>

                    <a href="{{ route('pelanggan.index') }}"
                    style="background:#6b7280;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;margin-left:10px;">
                    Kembali
                    </a>

</div>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>