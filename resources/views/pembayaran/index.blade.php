<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Pembayaran</h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto">
<div class="bg-white shadow rounded-lg p-6">

@if(session('success'))
<div class="mb-4 p-3 rounded bg-green-100 text-green-700">
{{ session('success') }}
</div>
@endif

<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
<thead class="bg-gray-100">
<tr>
<th class="px-4 py-2">No</th>
<th class="px-4 py-2">Invoice</th>
<th class="px-4 py-2">Pelanggan</th>
<th class="px-4 py-2">Periode</th>
<th class="px-4 py-2">Metode</th>
<th class="px-4 py-2 text-right">Total</th>
<th class="px-4 py-2">Status</th>
<th class="px-4 py-2">Tanggal</th>
<th class="px-4 py-2">Kasir</th>
<th class="px-4 py-2">Aksi</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-100">
@forelse($pembayarans as $item)
<tr>
<td class="px-4 py-2">{{ $loop->iteration + ($pembayarans->firstItem() - 1) }}</td>
<td class="px-4 py-2">{{ $item->tagihan->invoice_no }}</td>
<td class="px-4 py-2">{{ $item->tagihan->pelanggan->nama }}</td>
<td class="px-4 py-2">{{ $item->tagihan->periode }}</td>
<td class="px-4 py-2">{{ $item->metode }}</td>
<td class="px-4 py-2 text-right">Rp {{ number_format($item->total_bayar,0,',','.') }}</td>
<td class="px-4 py-2">
@if($item->isBerhasil())
<span class="px-2 py-1 rounded bg-green-600 text-white">Berhasil</span>
@elseif($item->isPending())
<span class="px-2 py-1 rounded bg-yellow-500 text-white">Pending</span>
@else
<span class="px-2 py-1 rounded bg-red-600 text-white">Dibatalkan</span>
@endif
</td>
<td class="px-4 py-2">{{ optional($item->tanggal_bayar)->format('d-m-Y') }}</td>
<td class="px-4 py-2">{{ $item->user->name ?? '-' }}</td>
<td class="px-4 py-2">
<a href="{{ route('pembayaran.show',$item) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Detail</a>
</td>
</tr>
@empty
<tr><td colspan="10" class="text-center py-6 text-gray-500">Belum ada data pembayaran.</td></tr>
@endforelse
</tbody>
</table>
</div>

<div class="mt-6">
{{ $pembayarans->links() }}
</div>

</div>
</div>
</div>
</x-app-layout>
