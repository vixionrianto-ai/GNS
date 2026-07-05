<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit PPP Profile - {{ $router->nama_router }}
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-3xl mx-auto">

            <div class="bg-white shadow rounded p-6">

                @if(session('error'))

                    <div style="
                        background:#fee2e2;
                        color:#991b1b;
                        padding:10px;
                        border-radius:5px;
                        margin-bottom:15px;">

                        {{ session('error') }}

                    </div>

                @endif

                <form method="POST"
                    action="{{ route('router.pppprofile.update', [$router->id, $data['.id']]) }}">

                    @csrf
                    @method('PUT')

                    <div style="margin-bottom:15px;">

                        <label>Nama Profile</label>

                        <input
                            type="text"
                            name="name"
                            value="{{ $data['name'] ?? '' }}"
                            style="
                                width:100%;
                                padding:10px;
                                border:1px solid #d1d5db;
                                border-radius:5px;">

                    </div>

                    <div style="margin-bottom:15px;">

                        <label>Local Address</label>

                        <input
                            type="text"
                            name="local_address"
                            value="{{ $data['local-address'] ?? '' }}"
                            style="
                                width:100%;
                                padding:10px;
                                border:1px solid #d1d5db;
                                border-radius:5px;">

                    </div>

                    <div style="margin-bottom:15px;">

                        <label>Remote Address</label>

                        <input
                            type="text"
                            name="remote_address"
                            value="{{ $data['remote-address'] ?? '' }}"
                            style="
                                width:100%;
                                padding:10px;
                                border:1px solid #d1d5db;
                                border-radius:5px;">

                    </div>

                    <div style="margin-bottom:15px;">

                        <label>Rate Limit</label>

                        <input
                            type="text"
                            name="rate_limit"
                            value="{{ $data['rate-limit'] ?? '' }}"
                            style="
                                width:100%;
                                padding:10px;
                                border:1px solid #d1d5db;
                                border-radius:5px;">

                    </div>

                    <div style="margin-bottom:20px;">

                        <label>Only One</label>

                        <select
                            name="only_one"
                            style="
                                width:100%;
                                padding:10px;
                                border:1px solid #d1d5db;
                                border-radius:5px;">

                            <option value="no"
                                {{ ($data['only-one'] ?? 'no') == 'no' ? 'selected' : '' }}>
                                No
                            </option>

                            <option value="yes"
                                {{ ($data['only-one'] ?? '') == 'yes' ? 'selected' : '' }}>
                                Yes
                            </option>

                        </select>

                    </div>

                    <a href="{{ route('router.pppprofile',$router->id) }}"
                        style="
                            background:#6b7280;
                            color:white;
                            padding:10px 20px;
                            border-radius:5px;
                            text-decoration:none;">

                        ← Kembali

                    </a>

                    <button
                        type="submit"
                        style="
                            background:#2563eb;
                            color:white;
                            padding:10px 20px;
                            border:none;
                            border-radius:5px;
                            cursor:pointer;
                            margin-left:10px;">

                        Simpan Perubahan

                    </button>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>