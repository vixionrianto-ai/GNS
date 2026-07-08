<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pembayaran</h2>
</x-slot>

<div class="py-6">
<div class="max-w-5xl mx-auto">
<div class="bg-white shadow rounded-lg p-6">

<div class="flex justify-between items-center mb-6">
<div>
<h3 class="text-2xl font-bold">{{ $pembayaran->tagihan->invoice_no }}</h3>
<p class="text-gray-500">
Tanggal Bayar :
{{ optional($pembayaran->tanggal_bayar)->format('d-m-Y') }}
</p>
</div>

@if($pembayaran->isBerhasil())
<span class="px-3 py-1 rounded bg-green-600 text-white">Berhasil</span>
@elseif($pembayaran->isPending())
<span class="px-3 py-1 rounded bg-yellow-500 text-white">Pending</span>
@else
<span class="px-3 py-1 rounded bg-red-600 text-white">Dibatalkan</span>
@endif
</div>

<table class="table-auto w-full">
<tbody>
<tr><td class="font-semibold py-2 w-56">Pelanggan</td><td>{{ $pembayaran->tagihan->pelanggan->nama }}</td></tr>
<tr><td class="font-semibold py-2">Username PPPoE</td><td>{{ $pembayaran->tagihan->pelanggan->username_pppoe }}</td></tr>
<tr><td class="font-semibold py-2">Paket</td><td>{{ $pembayaran->tagihan->pelanggan->paket->nama_paket ?? '-' }}</td></tr>
<tr><td class="font-semibold py-2">Metode</td><td>{{ $pembayaran->metode }}</td></tr>
<tr><td class="font-semibold py-2">Nominal</td><td>Rp {{ number_format($pembayaran->nominal,0,',','.') }}</td></tr>
<tr><td class="font-semibold py-2">Biaya Admin</td><td>Rp {{ number_format($pembayaran->biaya_admin,0,',','.') }}</td></tr>
<tr><td class="font-semibold py-2">Total Bayar</td><td>Rp {{ number_format($pembayaran->total_bayar,0,',','.') }}</td></tr>
<tr><td class="font-semibold py-2">Dibayar</td><td>Rp {{ number_format($pembayaran->dibayar,0,',','.') }}</td></tr>
<tr><td class="font-semibold py-2">Kembalian</td><td>Rp {{ number_format($pembayaran->kembalian,0,',','.') }}</td></tr>
<tr><td class="font-semibold py-2">Kasir</td><td>{{ $pembayaran->user->name ?? '-' }}</td></tr>
<tr><td class="font-semibold py-2">Keterangan</td><td>{{ $pembayaran->keterangan ?: '-' }}</td></tr>
</tbody>
</table>

<div class="mt-8 flex gap-3">
<a href="{{ route('pembayaran.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded">Kembali</a>
<button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded">Cetak</button>
</div>

</div>
</div>
</div>
</x-app-layout>
