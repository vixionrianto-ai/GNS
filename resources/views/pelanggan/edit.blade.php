<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Pelanggan</h2>
                <p class="text-sm text-gray-600">Perbarui data pelanggan dengan form yang lebih rapi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form action="{{ route('pelanggan.update', $pelanggan->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Nama</label>
                            <input type="text" name="nama" value="{{ $pelanggan->nama }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">No HP</label>
                            <input type="text" name="no_hp" value="{{ $pelanggan->no_hp }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Alamat</label>
                        <textarea name="alamat" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ $pelanggan->alamat }}</textarea>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Paket Internet</label>
                            <select name="paket_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($pakets as $paket)
                                    <option value="{{ $paket->id }}" {{ $pelanggan->paket_id == $paket->id ? 'selected' : '' }}>
                                        {{ $paket->nama_paket }} ({{ $paket->kecepatan }}) - Rp {{ number_format($paket->harga,0,',','.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Router MikroTik</label>
                            <select name="router_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ $pelanggan->router_id == $router->id ? 'selected' : '' }}>
                                        {{ $router->nama_router }} ({{ $router->ip_router }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Username PPPoE</label>
                            <input type="text" name="username_pppoe" value="{{ $pelanggan->username_pppoe }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Password PPPoE</label>
                            <input type="text" name="password_pppoe" value="{{ $pelanggan->password_pppoe }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">IP Address</label>
                            <input type="text" name="ip_address" value="{{ $pelanggan->ip_address }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">MAC Address</label>
                            <input type="text" name="mac_address" value="{{ $pelanggan->mac_address }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Pasang</label>
                            <input type="date" name="tanggal_pasang" value="{{ $pelanggan->tanggal_pasang }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Aktif</label>
                            <input type="date" name="tanggal_aktif" value="{{ $pelanggan->tanggal_aktif }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Aktif" {{ $pelanggan->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ $pelanggan->status == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 sm:flex-row">
                        <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 font-semibold text-white transition hover:bg-green-700">
                            Update
                        </button>
                        <a href="{{ route('pelanggan.index') }}" class="rounded-lg bg-gray-500 px-5 py-2.5 font-semibold text-white transition hover:bg-gray-600">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>