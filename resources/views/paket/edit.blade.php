<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Paket Internet</h2>
                <p class="text-sm text-gray-600">Perbarui data paket yang sudah ada.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form action="{{ route('paket.update', $paket->id) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Router</label>
                            <select id="router_id" name="router_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                @foreach($routers as $router)
                                    <option value="{{ $router->id }}" {{ $router->id == $paket->router_id ? 'selected' : '' }}>{{ $router->nama_router }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="Aktif" {{ $paket->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Nonaktif" {{ $paket->status == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Nama Paket</label>
                        <input type="text" name="nama_paket" value="{{ $paket->nama_paket }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">PPP Profile MikroTik</label>
                        <select id="profile_mikrotik" name="profile_mikrotik" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="{{ $paket->profile_mikrotik }}" selected>{{ $paket->profile_mikrotik }}</option>
                        </select>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Kecepatan</label>
                            <input type="text" id="kecepatan" name="kecepatan" value="{{ $paket->kecepatan }}" class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm" readonly>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Harga</label>
                            <input type="number" name="harga" value="{{ $paket->harga }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 sm:flex-row">
                        <a href="{{ route('paket.index') }}" class="rounded-lg bg-gray-500 px-5 py-2.5 font-semibold text-white transition hover:bg-gray-600">
                            ← Kembali
                        </a>
                        <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 font-semibold text-white transition hover:bg-green-700">
                            💾 Update Paket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function loadProfiles(routerId, selected = null) {
            fetch('/router/' + routerId + '/profiles')
                .then(response => response.json())
                .then(function(data){
                    let select = document.getElementById('profile_mikrotik');
                    select.innerHTML = '';

                    data.forEach(function(item){
                        let option = document.createElement('option');
                        option.value = item;
                        option.text = item;
                        if (item == selected) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                });
        }

        document.getElementById('router_id').addEventListener('change', function(){
            loadProfiles(this.value);
        });

        document.addEventListener('DOMContentLoaded', function(){
            loadProfiles(document.getElementById('router_id').value, "{{ $paket->profile_mikrotik }}");
        });

        document.getElementById('profile_mikrotik').addEventListener('change', function () {
            const profile = this.value;
            const match = profile.match(/\d+/);

            if (match) {
                document.getElementById('kecepatan').value = match[0] + ' Mbps';
            } else {
                document.getElementById('kecepatan').value = '';
            }
        });
    </script>
</x-app-layout>