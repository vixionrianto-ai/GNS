<x-app-layout>

<x-slot name="header">

<div class="flex items-center justify-between">

    <div>

        <h2 class="text-3xl font-bold text-slate-800">

            GNS NETWORK

        </h2>

        <p class="text-gray-500 mt-1">

            Billing Management System

        </p>

    </div>

    <div class="text-right">

        <div class="text-sm text-gray-500">

            Selamat Datang

        </div>

        <div class="font-bold text-lg">

            {{ Auth::user()->name }}

        </div>

        <div class="text-sm text-blue-600">

            Administrator

        </div>

        <div class="text-sm text-gray-500">

            {{ now()->translatedFormat('l, d F Y') }}

        </div>

        <div
            id="clock"
            class="font-bold text-xl text-indigo-600">

        </div>

    </div>

</div>

</x-slot>

<div class="py-6">

<div class="max-w-7xl mx-auto px-6">

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

{{-- Total Pelanggan --}}

<div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl shadow-lg p-6 text-white">

<div class="flex justify-between">

<div>

<div class="text-sm opacity-80">

Total Pelanggan

</div>

<div class="text-4xl font-bold mt-3">

{{ $totalPelanggan }}

</div>

</div>

<div class="text-5xl">

👥

</div>

</div>

</div>

{{-- Pelanggan Aktif --}}

<div class="bg-gradient-to-r from-green-600 to-green-500 rounded-2xl shadow-lg p-6 text-white">

<div class="flex justify-between">

<div>

<div class="text-sm opacity-80">

Pelanggan Aktif

</div>

<div class="text-4xl font-bold mt-3">

{{ $pelangganAktif }}

</div>

</div>

<div class="text-5xl">

🟢

</div>

</div>

</div>

{{-- Belum Bayar --}}

<div class="bg-gradient-to-r from-red-600 to-red-500 rounded-2xl shadow-lg p-6 text-white">

<div class="flex justify-between">

<div>

<div class="text-sm opacity-80">

Belum Bayar

</div>

<div class="text-4xl font-bold mt-3">

{{ $tagihanBelumBayar }}

</div>

</div>

<div class="text-5xl">

📄

</div>

</div>

</div>

{{-- Pendapatan Bulan Ini --}}

<div class="bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-2xl shadow-lg p-6 text-white">

<div class="flex justify-between">

<div>

<div class="text-sm opacity-80">

Pendapatan Bulan Ini

</div>

<div class="text-2xl font-bold mt-3">

Rp {{ number_format($pendapatanBulanIni,0,',','.') }}

</div>

</div>

<div class="text-5xl">

💰

</div>

</div>

</div>

{{-- Router Aktif --}}

<div class="bg-white rounded-2xl shadow border p-6">

<div class="flex justify-between items-center">

<div>

<div class="text-gray-500">

Router Aktif

</div>

<div class="text-4xl font-bold text-blue-600 mt-2">

{{ $routerAktif }}

</div>

</div>

<div class="text-5xl">

📡

</div>

</div>

</div>

{{-- Tagihan Lunas --}}

<div class="bg-white rounded-2xl shadow border p-6">

<div class="flex justify-between items-center">

<div>

<div class="text-gray-500">

Tagihan Lunas

</div>

<div class="text-4xl font-bold text-green-600 mt-2">

{{ $tagihanLunas }}

</div>

</div>

<div class="text-5xl">

✅

</div>

</div>

</div>

{{-- Pendapatan Hari Ini --}}

<div class="bg-white rounded-2xl shadow border p-6">

<div class="flex justify-between items-center">

<div>

<div class="text-gray-500">

Pendapatan Hari Ini

</div>

<div class="text-2xl font-bold text-indigo-600 mt-2">

Rp {{ number_format($pendapatanHariIni,0,',','.') }}

</div>

</div>

<div class="text-5xl">

💵

</div>

</div>

</div>

{{-- Pelanggan Nonaktif --}}

<div class="bg-white rounded-2xl shadow border p-6">

<div class="flex justify-between items-center">

<div>

<div class="text-gray-500">

Pelanggan Nonaktif

</div>

<div class="text-4xl font-bold text-red-600 mt-2">

{{ $pelangganNonaktif }}

</div>

</div>

<div class="text-5xl">

⛔

</div>

</div>

</div>

</div>

<br>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

<div class="lg:col-span-2 bg-white rounded-2xl shadow border p-6">

<h3 class="text-xl font-bold text-gray-700 mb-5">

📈 Pendapatan Bulanan

</h3>

<canvas id="pendapatanChart" height="120"></canvas>

</div>

<div class="bg-white rounded-2xl shadow border p-6">

<h3 class="text-xl font-bold text-gray-700 mb-5">

⚡ Quick Menu

</h3>
<div class="grid grid-cols-2 gap-3">

<a href="{{ route('pelanggan.create') }}"
class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white p-4 text-center transition">

<div class="text-3xl mb-2">

👤

</div>

<div class="font-semibold">

Tambah Pelanggan

</div>

</a>

<a href="{{ route('paket.create') }}"
class="rounded-xl bg-green-600 hover:bg-green-700 text-white p-4 text-center transition">

<div class="text-3xl mb-2">

🌐

</div>

<div class="font-semibold">

Tambah Paket

</div>

</a>

<a href="{{ route('tagihan.generate') }}"
class="rounded-xl bg-orange-500 hover:bg-orange-600 text-white p-4 text-center transition">

<div class="text-3xl mb-2">

📄

</div>

<div class="font-semibold">

Generate Tagihan

</div>

</a>

<a href="{{ route('pembayaran.index') }}"
class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white p-4 text-center transition">

<div class="text-3xl mb-2">

💳

</div>

<div class="font-semibold">

Pembayaran

</div>

</a>

<a href="{{ route('router.index') }}"
class="rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white p-4 text-center transition">

<div class="text-3xl mb-2">

📡

</div>

<div class="font-semibold">

Router

</div>

</a>

<a href="{{ route('tagihan.index') }}"
class="rounded-xl bg-red-600 hover:bg-red-700 text-white p-4 text-center transition">

<div class="text-3xl mb-2">

🧾

</div>

<div class="font-semibold">

Tagihan

</div>

</a>

</div>

</div>

</div>

<br>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

<div class="bg-white rounded-2xl shadow border p-6">

<h3 class="text-lg font-bold text-gray-700 mb-5">

📊 Status Pelanggan

</h3>

<canvas
id="statusChart"
height="240">

</canvas>

</div>

<div class="bg-white rounded-2xl shadow border p-6">

<h3 class="text-lg font-bold text-gray-700 mb-5">

🚀 Statistik Cepat

</h3>

<div class="space-y-4">

<div class="flex justify-between">

<span>Total Router</span>

<span class="font-bold">

{{ $totalRouter }}

</span>

</div>

<div class="flex justify-between">

<span>Router Aktif</span>

<span class="font-bold text-green-600">

{{ $routerAktif }}

</span>

</div>

<div class="flex justify-between">

<span>Tagihan Lunas</span>

<span class="font-bold text-blue-600">

{{ $tagihanLunas }}

</span>

</div>

<div class="flex justify-between">

<span>Belum Bayar</span>

<span class="font-bold text-red-600">

{{ $tagihanBelumBayar }}

</span>

</div>

<div class="flex justify-between">

<span>Pelanggan Aktif</span>

<span class="font-bold text-green-600">

{{ $pelangganAktif }}

</span>

</div>

<div class="flex justify-between">

<span>Pelanggan Nonaktif</span>

<span class="font-bold text-red-600">

{{ $pelangganNonaktif }}

</span>

</div>

</div>

</div>

<div class="bg-gradient-to-br from-blue-700 to-indigo-700 rounded-2xl shadow-lg text-white p-6">

<h3 class="text-xl font-bold mb-5">

💡 Informasi Sistem

</h3>

<div class="space-y-4">

<div>

<b>Versi</b>

<br>

GNS Billing v2.0

</div>

<div>

<b>Laravel</b>

<br>

13.x

</div>

<div>

<b>Status Sistem</b>

<br>

<span class="text-green-300">

ONLINE

</span>

</div>

<div>

<b>Server Time</b>

<br>

<span id="serverClock"></span>

</div>

</div>

</div>

</div>

<br>

<div class="bg-white rounded-2xl shadow border p-6">

<h2 class="text-xl font-bold text-gray-700 mb-4">

💳 Pembayaran Terakhir

</h2>

<div class="overflow-x-auto">

<table class="min-w-full">

<thead>

<tr class="bg-slate-100">

<th class="p-3 text-left">

Invoice

</th>

<th class="p-3 text-left">

Pelanggan

</th>

<th class="p-3 text-right">

Nominal

</th>

<th class="p-3 text-center">

Tanggal

</th>

<th class="p-3 text-center">

Status

</th>

</tr>

</thead>

<tbody>
    @forelse($pembayaranTerakhir as $item)

<tr class="border-b hover:bg-gray-50">

<td class="p-3">

{{ $item->invoice_no ?? $item->tagihan->invoice_no }}

</td>

<td class="p-3">

{{ $item->tagihan->pelanggan->nama }}

</td>

<td class="p-3 text-right font-semibold text-blue-600">

Rp {{ number_format($item->total_bayar,0,',','.') }}

</td>

<td class="p-3 text-center">

{{ \Carbon\Carbon::parse($item->tanggal_bayar)->format('d-m-Y') }}

</td>

<td class="p-3 text-center">

<span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">

LUNAS

</span>

</td>

</tr>

@empty

<tr>

<td colspan="5" class="text-center p-6 text-gray-500">

Belum ada pembayaran.

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>

<br>

<div class="bg-white rounded-2xl shadow border p-6">

<h2 class="text-xl font-bold text-gray-700 mb-4">

📄 Tagihan Jatuh Tempo

</h2>

<div class="overflow-x-auto">

<table class="min-w-full">

<thead>

<tr class="bg-red-50">

<th class="p-3 text-left">

Invoice

</th>

<th class="p-3 text-left">

Pelanggan

</th>

<th class="p-3 text-center">

Jatuh Tempo

</th>

<th class="p-3 text-center">

Status

</th>

</tr>

</thead>

<tbody>

@forelse($tagihanJatuhTempo as $item)

<tr class="border-b hover:bg-gray-50">

<td class="p-3">

{{ $item->invoice_no }}

</td>

<td class="p-3">

{{ $item->pelanggan->nama }}

</td>

<td class="p-3 text-center">

{{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->format('d-m-Y') }}

</td>

<td class="p-3 text-center">

<span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">

{{ strtoupper($item->status) }}

</span>

</td>

</tr>

@empty

<tr>

<td colspan="4" class="text-center p-6 text-gray-500">

Tidak ada tagihan jatuh tempo.

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

function updateClock(){

const now=new Date();

document.getElementById('clock').innerHTML=

now.toLocaleTimeString('id-ID');

document.getElementById('serverClock').innerHTML=

now.toLocaleString('id-ID');

}

setInterval(updateClock,1000);

updateClock();

const pendapatanChart=document.getElementById('pendapatanChart');

if(pendapatanChart){

new Chart(pendapatanChart,{

type:'bar',

data:{

labels:['Hari Ini','Bulan Ini'],

datasets:[{

label:'Pendapatan',

data:[

{{ $pendapatanHariIni }},

{{ $pendapatanBulanIni }}

]

}]

},

options:{

responsive:true,

plugins:{

legend:{display:false}

}

}

});

}

const statusChart=document.getElementById('statusChart');

if(statusChart){

new Chart(statusChart,{

type:'doughnut',

data:{

labels:['Aktif','Nonaktif'],

datasets:[{

data:[

{{ $pelangganAktif }},

{{ $pelangganNonaktif }}

]

}]

},

options:{

responsive:true

}

});

}

</script>

</x-app-layout>