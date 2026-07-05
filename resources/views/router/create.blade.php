<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Router MikroTik
        </h2>
    </x-slot>

<div class="py-6">

<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded p-6">

<form action="{{ route('router.store') }}" method="POST">

@csrf

<label>Nama Router</label>
<input type="text" name="nama_router" class="border rounded w-full p-2 mb-3" required>

<label>IP Router</label>
<input type="text" name="ip_router" class="border rounded w-full p-2 mb-3" placeholder="192.168.88.1">

<label>API Port</label>
<input type="number" name="api_port" value="8728" class="border rounded w-full p-2 mb-3">

<label>Username</label>
<input type="text" name="username" class="border rounded w-full p-2 mb-3">

<label>Password</label>
<input type="password" name="password" class="border rounded w-full p-2 mb-3">

<label>Lokasi</label>
<input type="text" name="lokasi" class="border rounded w-full p-2 mb-3">

<label>Versi RouterOS</label>
<input type="text" name="versi_routeros" class="border rounded w-full p-2 mb-3" placeholder="7.19">

<div class="mb-4">

<label>

<input type="checkbox" name="ssl">

Gunakan SSL

</label>

</div>

<label>Status</label>

<select name="status" class="border rounded w-full p-2 mb-4">

<option value="Aktif">Aktif</option>

<option value="Nonaktif">Nonaktif</option>

</select>

<button type="submit"
style="background:#16a34a;color:white;padding:10px 20px;border:none;border-radius:6px;">

Simpan

</button>

<a href="{{ route('router.index') }}"
style="background:#6b7280;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;">

Kembali

</a>

</form>

</div>

</div>

</div>

</x-app-layout>