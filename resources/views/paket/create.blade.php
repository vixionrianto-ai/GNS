<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Tambah Paket</h2>
                <p class="text-sm text-gray-600">Buat paket internet baru dengan data yang terstruktur.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                @if(session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4">
                        <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('paket.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Router</label>
                            <select id="router_id" name="router_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Router --</option>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}">{{ $router->nama_router }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Nama Paket</label>
                        <input type="text" name="nama_paket" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">PPP Profile MikroTik</label>
                        <select id="profile_mikrotik" name="profile_mikrotik" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">-- Pilih Router terlebih dahulu --</option>
                        </select>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Kecepatan</label>
                            <input type="text" id="kecepatan" name="kecepatan" class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm" readonly required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Harga</label>
                            <input type="number" name="harga" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <hr>

                    <div class="mt-3 d-flex justify-content-between">

                        <a href="{{ route('paket.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ← Kembali
                        </a>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> 💾 Simpan Paket
                        </button>

                    </div>

                    
                </form>
            </div>
        </div>
    </div>

    <script>
        const routerSelect = document.getElementById('router_id');
        const profileSelect = document.getElementById('profile_mikrotik');

        routerSelect.addEventListener('change', function () {
            let routerId = this.value;

            if (routerId == '') {
                profileSelect.innerHTML = '<option value="">Pilih Router terlebih dahulu</option>';
                return;
            }

            profileSelect.innerHTML = '<option value="">Memuat profile...</option>';

            fetch('/router/' + routerId + '/profiles')
                .then(response => response.json())
                .then(data => {
                    profileSelect.innerHTML = '<option value="">Pilih Profile</option>';
                    data.forEach(function(profile){
                        profileSelect.innerHTML += `<option value="${profile}">${profile}</option>`;
                    });
                })
                .catch(function(){
                    profileSelect.innerHTML = '<option value="">Gagal mengambil profile</option>';
                });
        });

        profileSelect.addEventListener('change', function () {
            let profile = this.value.trim();
            let match = profile.match(/^C(\d+)$/i);

            if (match) {
                document.getElementById('kecepatan').value = match[1] + ' Mbps';
            } else {
                document.getElementById('kecepatan').value = '';
            }
        });
    </script>
</x-app-layout>