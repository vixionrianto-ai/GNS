<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Paket Internet
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-4xl mx-auto">

            <div class="bg-white shadow rounded-lg p-6">

                <form action="{{ route('paket.update',$paket->id) }}" method="POST">

                    @csrf
                    @method('PUT')

                    {{-- Router --}}
                    <div class="mb-4">

                        <label class="font-semibold">
                            Router
                        </label>

                        <select
                            id="router_id"
                            name="router_id"
                            class="w-full border rounded p-2"
                            required>

                            @foreach($routers as $router)

                                <option
                                    value="{{ $router->id }}"
                                    {{ $router->id==$paket->router_id?'selected':'' }}>

                                    {{ $router->nama_router }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- Nama Paket --}}
                    <div class="mb-4">

                        <label class="font-semibold">
                            Nama Paket
                        </label>

                        <input
                            type="text"
                            name="nama_paket"
                            value="{{ $paket->nama_paket }}"
                            class="w-full border rounded p-2"
                            required>

                    </div>

                    {{-- PPP Profile --}}
                    <div class="mb-4">

                        <label class="font-semibold">
                            PPP Profile MikroTik
                        </label>

                        <select
                            id="profile_mikrotik"
                            name="profile_mikrotik"
                            class="w-full border rounded p-2"
                            required>

                            <option
                                value="{{ $paket->profile_mikrotik }}"
                                selected>

                                {{ $paket->profile_mikrotik }}

                            </option>

                        </select>

                    </div>

                    {{-- Kecepatan --}}
                    <div class="mb-4">

                        <label class="font-semibold">
                            Kecepatan
                        </label>

                        <input
                            type="text"
                            id="kecepatan"
                            name="kecepatan"
                            value="{{ $paket->kecepatan }}"
                            class="w-full border rounded p-2"
                            readonly>

                    </div>

                    {{-- Harga --}}
                    <div class="mb-4">

                        <label class="font-semibold">
                            Harga
                        </label>

                        <input
                            type="number"
                            name="harga"
                            value="{{ $paket->harga }}"
                            class="w-full border rounded p-2"
                            required>

                    </div>

                    {{-- Status --}}
                    <div class="mb-4">

                        <label class="font-semibold">
                            Status
                        </label>

                        <select
                            name="status"
                            class="w-full border rounded p-2">

                            <option value="Aktif"
                                {{ $paket->status=='Aktif'?'selected':'' }}>

                                Aktif

                            </option>

                            <option value="Nonaktif"
                                {{ $paket->status=='Nonaktif'?'selected':'' }}>

                                Nonaktif

                            </option>

                        </select>

                    </div>

                    <div class="flex items-center gap-3 mt-6">

                        <a href="{{ route('paket.index') }}"
                           class="px-5 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg shadow">

                            ← Kembali

                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow">

                            <span class="bg-green-800 px-2 py-1 rounded">
                                💾
                            </span>

                            <span>
                                Update Paket
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

<script>

function loadProfiles(routerId, selected = null)
{
    fetch('/router/' + routerId + '/profiles')

        .then(response => response.json())

        .then(function(data){

            let select =
                document.getElementById('profile_mikrotik');

            select.innerHTML='';

            data.forEach(function(item){

                let option=document.createElement('option');

                option.value=item;

                option.text=item;

                if(item==selected){

                    option.selected=true;

                }

                select.appendChild(option);

            });

        });

}

document.getElementById('router_id').addEventListener('change',function(){

    loadProfiles(this.value);

});

document.addEventListener('DOMContentLoaded',function(){

    loadProfiles(
        document.getElementById('router_id').value,
        "{{ $paket->profile_mikrotik }}"
    );

});

document.getElementById('profile_mikrotik').addEventListener('change', function () {

    const profile = this.value;

    const match = profile.match(/\d+/);

    if (match) {

        document.getElementById('kecepatan').value =
            match[0] + ' Mbps';

    } else {

        document.getElementById('kecepatan').value = '';

    }

});

</script>

</x-app-layout>