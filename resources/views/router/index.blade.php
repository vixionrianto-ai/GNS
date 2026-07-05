<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Router MikroTik
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <a href="{{ route('router.create') }}"
               style="background:#2563eb;color:white;padding:10px 15px;border-radius:5px;text-decoration:none;">
                + Tambah Router
            </a>

            @if(session('success'))
                <div style="background:#d4edda;color:#155724;padding:10px;margin-top:10px;border-radius:5px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background:#f8d7da;color:#721c24;padding:10px;margin-top:10px;border-radius:5px;">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow rounded mt-4 p-4">

                <table class="table-auto w-full border">

                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-2">No</th>
                            <th class="border p-2">Nama Router</th>
                            <th class="border p-2">IP Router</th>
                            <th class="border p-2">API Port</th>
                            <th class="border p-2">Lokasi</th>
                            <th class="border p-2">Versi</th>
                            <th class="border p-2">SSL</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($routers as $router)

                        <tr>

                            <td class="border p-2">{{ $loop->iteration }}</td>

                            <td class="border p-2">{{ $router->nama_router }}</td>

                            <td class="border p-2">{{ $router->ip_router }}</td>

                            <td class="border p-2">{{ $router->api_port }}</td>

                            <td class="border p-2">{{ $router->lokasi }}</td>

                            <td class="border p-2">{{ $router->versi_routeros }}</td>

                            <td class="border p-2">
                                {{ $router->ssl ? 'Ya' : 'Tidak' }}
                            </td>

                            <td class="border p-2">
                                {{ $router->status }}
                            </td>

                            <td class="border p-2">

                                <a href="{{ route('router.edit',$router->id) }}"
                                   style="background:#f59e0b;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;">
                                    Edit
                                </a>

                                <a href="{{ route('router.test',$router->id) }}"
                                   style="background:#16a34a;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;margin-left:5px;">
                                    Test
                                </a>
                                <a href="{{ route('router.pppsecret',$router->id) }}"
                                    style="background:#2563eb;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;margin-left:5px;">
                                    PPP Secret
                                </a>
                                <form action="{{ route('router.destroy',$router->id) }}"
                                      method="POST"
                                      style="display:inline;">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Hapus router ini?')"
                                            style="background:#dc2626;color:white;padding:6px 12px;border:none;border-radius:5px;cursor:pointer;margin-left:5px;">
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="9" class="text-center p-4">
                                Belum ada Router.
                            </td>
                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</x-app-layout>
```
