<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data PPP Secret - {{ $router->nama_router }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Tombol Atas --}}
            <div style="
                display:flex;
                align-items:center;
                gap:12px;
                margin-bottom:20px;">

                <a href="{{ route('router.index') }}"
                    style="
                        background:#2563eb;
                        color:white;
                        padding:10px 18px;
                        border-radius:6px;
                        text-decoration:none;
                        font-weight:500;">

                    ← Kembali

                </a>

                <a href="{{ route('router.pppsecret.create',$router->id) }}"
                    style="
                        background:#16a34a;
                        color:white;
                        padding:10px 18px;
                        border-radius:6px;
                        text-decoration:none;
                        font-weight:500;">

                    + Tambah PPP Secret

                </a>

            </div>
            <div class="bg-white shadow rounded p-4">
                <table class="table-auto w-full border">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-2">No</th>
                            <th class="border p-2">Username</th>
                            <th class="border p-2">Password</th>
                            <th class="border p-2">Service</th>
                            <th class="border p-2">Profile</th>
                            <th class="border p-2">Disabled</th>
                            <th class="border p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($secrets as $secret)
                        <tr>
                            <td class="border p-2">
                                {{ $loop->iteration }}
                            </td>                   
                            <td class="border p-2">
                                {{ $secret['name'] ?? '-' }}
                        </td>
                            <td class="border p-2">
                                {{ $secret['password'] ?? '-' }}
                            </td>
                            <td class="border p-2">
                                {{ $secret['service'] ?? '-' }}
                            </td>
                            <td class="border p-2">
                                {{ $secret['profile'] ?? '-' }}
                            </td>
                            <td class="border p-2">
                                @if(($secret['disabled'] ?? 'false') == 'true')
                                    <span style="color:red;font-weight:bold;">
                                        Disabled
                                    </span>
                                @else
                                    <span style="color:green;font-weight:bold;">
                                        Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="border p-2">
                            <a href="{{ route('router.pppsecret.edit', [$router->id, $secret['name']]) }}"
                                    style="background:#f59e0b;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;">
                                    Edit
                            </a>
                            {{-- ENABLE / DISABLE --}}
@if(($secret['disabled'] ?? 'false') == 'true')

<form action="{{ route('router.pppsecret.enable', [$router->id, $secret['.id']]) }}"
    method="POST"
    style="display:inline;">

    @csrf
    @method('PUT')

    <button type="submit"
        style="background:#16a34a;
               color:white;
               padding:5px 10px;
               border:none;
               border-radius:5px;
               cursor:pointer;
               margin-left:3px;">

        Enable

    </button>

</form>

@else

<form action="{{ route('router.pppsecret.disable', [$router->id, $secret['.id']]) }}"
    method="POST"
    style="display:inline;">

    @csrf
    @method('PUT')

    <button type="submit"
        style="background:#f59e0b;
               color:white;
               padding:5px 10px;
               border:none;
               border-radius:5px;
               cursor:pointer;
               margin-left:3px;">

        Disable

    </button>

</form>

@endif
                            </a>
                            <a>
                                <form action="{{ route('router.pppsecret.delete', [$router->id, $secret['.id']]) }}"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Yakin ingin menghapus PPP Secret {{ $secret['name'] }}?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        style="background:#dc2626;
                                            color:white;
                                            padding:5px 10px;
                                            border:none;
                                            border-radius:5px;
                                            cursor:pointer;">
                                        Hapus
                                    </button>
                                </form>    
                            </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border p-3 text-center">
                                Tidak ada PPP Secret.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>