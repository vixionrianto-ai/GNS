<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Pelanggan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('pelanggan.update', $pelanggan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label>Nama</label>
                        <input type="text"
                               name="nama"
                               value="{{ $pelanggan->nama }}"
                               class="border rounded w-full p-2"
                               required>
                    </div>

                    <div class="mb-4">
                        <label>Alamat</label>
                        <textarea name="alamat"
                                  class="border rounded w-full p-2"
                                  required>{{ $pelanggan->alamat }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label>No HP</label>
                        <input type="text"
                               name="no_hp"
                               value="{{ $pelanggan->no_hp }}"
                               class="border rounded w-full p-2"
                               required>
                    </div>

                    <div class="mb-4">
                        <label>Paket Internet</label>

                        <select name="paket_id" class="border rounded w-full p-2">

                            @foreach($pakets as $paket)

                                <option value="{{ $paket->id }}"
                                    {{ $pelanggan->paket_id == $paket->id ? 'selected' : '' }}>

                                    {{ $paket->nama_paket }}
                                    ({{ $paket->kecepatan }})
                                    - Rp {{ number_format($paket->harga,0,',','.') }}

                                </option>

                            @endforeach

                        </select>

                    </div>
                    <div class="mb-4">

                        <label>Router MikroTik</label>

                        <select
                            name="router_id"
                            class="border rounded w-full p-2"
                            required>

                            @foreach($routers as $router)

                                <option
                                    value="{{ $router->id }}"
                                    {{ $pelanggan->router_id == $router->id ? 'selected' : '' }}>

                                    {{ $router->nama_router }}
                                    ({{ $router->ip_router }})

                                </option>

                            @endforeach

                        </select>

                    </div>
                    
                    <div class="mb-4">
                        <label>Username PPPoE</label>
                        <input type="text"
                            name="username_pppoe"
                            value="{{ $pelanggan->username_pppoe }}"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label>Password PPPoE</label>
                        <input type="text"
                            name="password_pppoe"
                            value="{{ $pelanggan->password_pppoe }}"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label>IP Address</label>
                        <input type="text"
                            name="ip_address"
                            value="{{ $pelanggan->ip_address }}"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label>MAC Address</label>
                        <input type="text"
                            name="mac_address"
                            value="{{ $pelanggan->mac_address }}"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label>Tanggal Pasang</label>
                        <input type="date"
                            name="tanggal_pasang"
                            value="{{ $pelanggan->tanggal_pasang }}"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label>Tanggal Aktif</label>
                        <input type="date"
                            name="tanggal_aktif"
                            value="{{ $pelanggan->tanggal_aktif }}"
                            class="border rounded w-full p-2">
                    </div>

                    <div class="mb-4">
                        <label>Status</label>

                        <select name="status" class="border rounded w-full p-2">

                            <option value="Aktif"
                                {{ $pelanggan->status=='Aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="Nonaktif"
                                {{ $pelanggan->status=='Nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>

                        </select>

                    </div>

                    <button type="submit"
                        style="background:#16a34a;color:white;padding:10px 20px;border:none;border-radius:6px;">
                        Update
                    </button>

                    <a href="{{ route('pelanggan.index') }}"
                       style="background:#6b7280;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;">
                        Kembali
                    </a>

                </form>

            </div>

        </div>
    </div>
</x-app-layout>