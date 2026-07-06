<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Tambah Pelanggan</h2>
                <p class="text-sm text-gray-600">Isi data pelanggan baru dengan form yang lebih terstruktur.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form action="{{ route('pelanggan.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Nama</label>
                            <input type="text" name="nama" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">No HP</label>
                            <input type="text" name="no_hp" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Alamat</label>
                        <textarea name="alamat" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required></textarea>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Paket Internet</label>
                            <select name="paket_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach($pakets as $paket)
                                    <option value="{{ $paket->id }}">
                                        {{ $paket->nama_paket }} ({{ $paket->kecepatan }}) - Rp {{ number_format($paket->harga,0,',','.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Router MikroTik</label>
                            <select name="router_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Router --</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}">
                                        {{ $router->nama_router }} ({{ $router->ip_router }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Username PPPoE</label>
                            <input type="text" name="username_pppoe" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Password PPPoE</label>
                            <input type="text" name="password_pppoe" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">IP Address</label>
                            <input type="text" name="ip_address" placeholder="Kosongkan jika DHCP" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">MAC Address</label>
                            <input type="text" name="mac_address" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Pasang</label>
                            <input type="date" name="tanggal_pasang" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Aktif</label>
                            <input type="date" name="tanggal_aktif" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    
                    <hr>

                    <div class="row mt-4">

                        <div class="col-md-6 mb-2">
                            <a href="{{ route('pelanggan.index') }}"
                            class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </a>
                        </div>

                        <div class="col-md-6 mb-2">
                            <button type="submit"
                                    class="btn btn-success btn-lg w-100">
                                <i class="fas fa-save"></i>
                                Simpan Pelanggan
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
    
</x-app-layout>