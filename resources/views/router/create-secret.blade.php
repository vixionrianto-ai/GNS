<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah PPP Secret
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded p-6">

                <form action="{{ route('router.pppsecret.store',$router->id) }}" method="POST">

                    @csrf

                    <div class="mb-4">

                        <label class="block font-semibold">
                            Username
                        </label>

                        <input type="text"
                            name="username"
                            class="border rounded w-full p-2"
                            required>

                    </div>

                    <div class="mb-4">

                        <label class="block font-semibold">
                            Password
                        </label>

                        <input type="text"
                            name="password"
                            class="border rounded w-full p-2"
                            required>

                    </div>

                    <div class="mb-4">

                        <label class="block font-semibold">
                            Service
                        </label>

                        <select name="service"
                            class="border rounded w-full p-2">

                            <option value="pppoe">PPPoE</option>
                            <option value="any">Any</option>

                        </select>

                    </div>

                    <div class="mb-4">

                        <label class="block font-semibold">
                            Profile
                        </label>

                        <select name="profile"
                            class="border rounded w-full p-2">

                            @foreach($profiles as $profile)

                                <option value="{{ $profile['name'] }}">
                                    {{ $profile['name'] }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mt-5">

                        <button type="submit"
                            style="background:#16a34a;color:white;padding:10px 20px;border:none;border-radius:5px;">

                            Simpan

                        </button>

                        <a href="{{ route('router.pppsecret',$router->id) }}"
                            style="background:#6b7280;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;margin-left:10px;">

                            Kembali

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</x-app-layout>