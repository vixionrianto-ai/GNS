<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah PPP Profile - {{ $router->nama_router }}
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-3xl mx-auto">

            <div class="bg-white shadow rounded p-6">
        @if(session('error'))

        <div style="background:#fee2e2;color:#991b1b;padding:10px;margin-bottom:10px;border-radius:5px;">

            {{ session('error') }}

        </div>

        @endif

                <form method="POST"
                    action="{{ route('router.pppprofile.store',$router->id) }}">

                    @csrf

                    <div class="mb-3">
                        <label>Nama Profile</label>

                        <input
                            type="text"
                            name="name"
                            class="w-full border rounded p-2"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Local Address</label>

                        <input
                            type="text"
                            name="local_address"
                            class="w-full border rounded p-2">
                    </div>

                    <div class="mb-3">
                        <label>Remote Address</label>

                        <input
                            type="text"
                            name="remote_address"
                            class="w-full border rounded p-2">
                    </div>

                    <div class="mb-3">
                        <label>Rate Limit</label>

                        <input
                            type="text"
                            name="rate_limit"
                            placeholder="10M/10M"
                            class="w-full border rounded p-2">
                    </div>

                    <div class="mb-3">

                        <label>Only One</label>

                        <select
                            name="only_one"
                            class="w-full border rounded p-2">

                            <option value="no">No</option>

                            <option value="yes">Yes</option>

                        </select>

                    </div>

                    <a href="{{ route('router.pppprofile',$router->id) }}"
                style="
                    background:#6b7280;
                    color:white;
                    padding:10px 20px;
                    border-radius:5px;
                    text-decoration:none;">

                        Kembali

                    </a>

                    <button type="submit"
                    style="
                        background:#2563eb;
                        color:white;
                        padding:10px 20px;
                        border:none;
                        border-radius:5px;
                        cursor:pointer;
                        margin-left:10px;">

                    Simpan

                </button>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>