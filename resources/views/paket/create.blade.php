<x-app-layout>

<div class="max-w-4xl mx-auto">

    <div class="bg-white shadow rounded-lg p-6">

        <h2 class="text-2xl font-bold mb-6">
            Tambah Paket
        </h2>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('paket.store') }}" method="POST">

            @csrf

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

                    <option value="">
                        -- Pilih Router --
                    </option>

                    @foreach($routers as $router)

                        <option
                            value="{{ $router->id }}">

                            {{ $router->nama_router }}

                        </option>

                    @endforeach

                </select>

            </div>


            <div class="mb-4">

            <label class="font-semibold">

                Status

            </label>

            <select
                name="status"
                class="w-full border rounded p-2"
                required>

                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>

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

                    <option value="">

                        -- Pilih Router terlebih dahulu --

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
                    class="w-full border rounded p-2"
                    readonly
                    required>

            </div>

            {{-- Harga --}}
            <div class="mb-4">

                <label class="font-semibold">

                    Harga

                </label>

                <input
                    type="number"
                    name="harga"
                    class="w-full border rounded p-2"
                    required>

            </div>

            {{-- Keterangan --}}
            <div class="mb-4">

                <label class="font-semibold">

                    Keterangan

                </label>

                <textarea
                    name="keterangan"
                    rows="3"
                    class="w-full border rounded p-2"></textarea>

            </div>

            <div class="flex items-center gap-3 mt-6">

    <a href="{{ route('paket.index') }}"
       class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded">

        ← Kembali

    </a>

    <button
        type="submit"
        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded shadow">

        💾 Simpan Paket

    </button>

</div>

        </form>

    </div>

</div>

<script>

const routerSelect = document.getElementById('router_id');
const profileSelect = document.getElementById('profile_mikrotik');

routerSelect.addEventListener('change', function () {

    let routerId = this.value;

    if(routerId == ''){

        profileSelect.innerHTML =
            '<option value="">Pilih Router terlebih dahulu</option>';

        return;

    }

    profileSelect.innerHTML =
        '<option value="">Memuat profile...</option>';

    fetch('/router/' + routerId + '/profiles')

        .then(response => response.json())

        .then(data => {

            profileSelect.innerHTML =
                '<option value="">Pilih Profile</option>';

            data.forEach(function(profile){

                profileSelect.innerHTML +=
                    `<option value="${profile}">${profile}</option>`;

            });

        })

        .catch(function(){

            profileSelect.innerHTML =
                '<option value="">Gagal mengambil profile</option>';

        });

});


/*
|--------------------------------------------------------------------------
| OTOMATIS ISI KECEPATAN
|--------------------------------------------------------------------------
*/

profileSelect.addEventListener('change', function () {

    console.log("Profile dipilih:", this.value);

    let profile = this.value.trim();

    let match = profile.match(/^C(\d+)$/i);

    console.log(match);

    if(match){

        document.getElementById('kecepatan').value =
            match[1] + ' Mbps';

    }else{

        document.getElementById('kecepatan').value = '';

    }

});

</script>

</x-app-layout>