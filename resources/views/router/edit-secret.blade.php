<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit PPP Secret
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">
                <div class="mb-4 rounded border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Password PPP hanya boleh diisi ulang saat ingin mengubahnya. Password lama tidak ditampilkan di form.
                </div>

                <form action="{{ route('router.pppsecret.update', [$router->id, $secret['name']]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-semibold">Username</label>
                        <input type="text"
                            name="username"
                            value="{{ $secret['name'] }}"
                            class="border rounded w-full p-2"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Password Baru</label>
                        <input type="password"
                            name="password"
                            value=""
                            class="border rounded w-full p-2"
                            autocomplete="new-password"
                            required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Service</label>
                        <select name="service" class="border rounded w-full p-2">
                            <option value="pppoe" {{ ($secret['service'] ?? '') == 'pppoe' ? 'selected' : '' }}>PPPoE</option>
                            <option value="any" {{ ($secret['service'] ?? '') == 'any' ? 'selected' : '' }}>Any</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Profile</label>
                        <select name="profile" class="border rounded w-full p-2" required>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile['name'] }}" {{ ($secret['profile'] ?? '') == $profile['name'] ? 'selected' : '' }}>
                                    {{ $profile['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block font-semibold">Status</label>
                        <select name="disabled" class="border rounded w-full p-2" required>
                            <option value="false" {{ ($secret['disabled'] ?? 'false') == 'false' ? 'selected' : '' }}>Aktif</option>
                            <option value="true" {{ ($secret['disabled'] ?? 'false') == 'true' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                            style="background:#16a34a;color:white;padding:10px 20px;border:none;border-radius:5px;">
                            Update
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
