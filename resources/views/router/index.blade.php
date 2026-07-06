<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Data Router MikroTik</h2>
                <p class="text-sm text-gray-600">Kelola router, uji koneksi, dan lihat PPP secret.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('router.create') }}" class="rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    ➕ Tambah Router
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama Router</th>
                                <th class="px-4 py-3 text-left">IP Router</th>
                                <th class="px-4 py-3 text-left">API Port</th>
                                <th class="px-4 py-3 text-left">Lokasi</th>
                                <th class="px-4 py-3 text-left">Versi</th>
                                <th class="px-4 py-3 text-left">SSL</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routers as $router)
                                <tr class="border-t border-gray-200">
                                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $router->nama_router }}</td>
                                    <td class="px-4 py-3">{{ $router->ip_router }}</td>
                                    <td class="px-4 py-3">{{ $router->api_port }}</td>
                                    <td class="px-4 py-3">{{ $router->lokasi }}</td>
                                    <td class="px-4 py-3">{{ $router->versi_routeros }}</td>
                                    <td class="px-4 py-3">{{ $router->ssl ? 'Ya' : 'Tidak' }}</td>
                                    <td class="px-4 py-3">{{ $router->status }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('router.edit', $router->id) }}" class="rounded-lg bg-yellow-500 px-3 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600">Edit</a>
                                            <a href="{{ route('router.test', $router->id) }}" class="rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-green-700">Test</a>
                                            <a href="{{ route('router.pppsecret', $router->id) }}" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">PPP Secret</a>
                                            <form action="{{ route('router.destroy', $router->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Hapus router ini?')" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada Router.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```
