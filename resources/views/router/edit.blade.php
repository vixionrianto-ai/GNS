<x-app-layout>

<x-slot name="header">

<h2 class="font-semibold text-xl text-gray-800 leading-tight">

Edit Router MikroTik

</h2>

</x-slot>

<div class="py-6">

<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded p-6">

<form action="{{ route('router.update',$router->id) }}" method="POST">

@csrf
@method('PUT')

<label>Nama Router</label>

<input type="text"
name="nama_router"
value="{{ $router->nama_router }}"
class="border rounded w-full p-2 mb-3">

<label>IP Router</label>

<input type="text"
name="ip_router"
value="{{ $router->ip_router }}"
class="border rounded w-full p-2 mb-3">

<label>API Port</label>

<input type="number"
name="api_port"
value="{{ $router->api_port }}"
class="border rounded w-full p-2 mb-3">

<label>Username</label>

<input type="text"
name="username"
value="{{ $router->username }}"
class="border rounded w-full p-2 mb-3">

<label>Password</label>

<input type="text"
name="password"
value="{{ $router->password }}"
class="border rounded w-full p-2 mb-3">

<label>Lokasi</label>

<input type="text"
name="lokasi"
value="{{ $router->lokasi }}"
class="border rounded w-full p-2 mb-3">

<label>Versi RouterOS</label>

<input type="text"
name="versi_routeros"
value="{{ $router->versi_routeros }}"
class="border rounded w-full p-2 mb-3">

<div class="mb-4">

<label>

<input type="checkbox"
name="ssl"
{{ $router->ssl ? 'checked' : '' }}>

Gunakan SSL

</label>

</div>

<select name="status" class="border rounded w-full p-2 mb-4">

<option value="Aktif"
{{ $router->status=='Aktif'?'selected':'' }}>
Aktif
</option>

<option value="Nonaktif"
{{ $router->status=='Nonaktif'?'selected':'' }}>
Nonaktif
</option>

</select>

<button type="submit"
style="background:#2563eb;color:white;padding:10px 20px;border:none;border-radius:6px;">

Update

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