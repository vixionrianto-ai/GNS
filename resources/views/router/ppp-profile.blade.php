<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data PPP Profile - {{ $router->nama_router }}
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
                        font-weight:500;
                        display:inline-block;">

                    ← Kembali

                </a>

                <a href="{{ route('router.pppprofile.create',$router->id) }}"
                    style="
                        background:#16a34a;
                        color:white;
                        padding:10px 18px;
                        border-radius:6px;
                        text-decoration:none;
                        font-weight:500;
                        display:inline-block;">

                    + Tambah PPP Profile

                </a>

            </div>

            {{-- Pesan --}}
            @if(session('success'))
                <div style="background:#dcfce7;color:#166534;padding:10px;border-radius:5px;margin-bottom:10px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:5px;margin-bottom:10px;">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow rounded p-4">

                <table class="table-auto w-full border">

                    <thead>

                        <tr class="bg-gray-200">

                            <th class="border p-2">No</th>

                            <th class="border p-2">Profile</th>

                            <th class="border p-2">Local Address</th>

                            <th class="border p-2">Remote Address</th>

                            <th class="border p-2">Rate Limit</th>

                            <th class="border p-2">Only One</th>

                            <th class="border p-2">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($profiles as $profile)

                        <tr>

                            <td class="border p-2">

                                {{ $loop->iteration }}

                            </td>

                            <td class="border p-2">

                                {{ $profile['name'] ?? '-' }}

                            </td>

                            <td class="border p-2">

                                {{ $profile['local-address'] ?? '-' }}

                            </td>

                            <td class="border p-2">

                                {{ $profile['remote-address'] ?? '-' }}

                            </td>

                            <td class="border p-2">

                                {{ $profile['rate-limit'] ?? '-' }}

                            </td>

                            <td class="border p-2">

                                {{ $profile['only-one'] ?? 'no' }}

                            </td>

                           <td class="border p-2">

                            <div style="display:flex; gap:8px; justify-content:center;">

                                <a href="{{ route('router.pppprofile.edit', [$router->id, $profile['.id']]) }}"
                                    style="
                                        background:#f59e0b;
                                        color:white;
                                        padding:6px 14px;
                                        border-radius:5px;
                                        text-decoration:none;
                                        display:inline-block;">

                                    Edit

                                </a>

                                <form action="{{ route('router.pppprofile.delete', [$router->id, $profile['.id']]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus PPP Profile {{ $profile['name'] }} ?')"
                                    style="margin:0;">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        style="
                                            background:#dc2626;
                                            color:white;
                                            padding:6px 14px;
                                            border:none;
                                            border-radius:5px;
                                            cursor:pointer;">

                                        Hapus

                                    </button>

                                </form>

                            </div>

                        </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="border p-3 text-center">

                                Tidak ada PPP Profile.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-app-layout>