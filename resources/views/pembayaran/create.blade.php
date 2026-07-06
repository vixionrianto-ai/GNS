<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Pembayaran Tagihan</h2>
                <p class="text-sm text-gray-600">Isi data pembayaran dengan tampilan yang lebih rapi dan mudah dibaca.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-6 rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $tagihan->invoice_no }}</h3>
                            <p class="text-base text-gray-700">{{ $tagihan->pelanggan->nama }}</p>
                        </div>
                        <div class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                            @if($tagihan->status == 'Lunas')
                                Lunas
                            @else
                                Belum Bayar
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-8 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                        <h4 class="mb-4 text-lg font-semibold text-gray-900">Informasi Pelanggan</h4>
                        <dl class="space-y-3 text-sm text-gray-700">
                            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
                                <dt class="font-semibold text-gray-900">Router</dt>
                                <dd class="text-right">{{ $tagihan->pelanggan->router->nama_router ?? '-' }}</dd>
                            </div>
                            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
                                <dt class="font-semibold text-gray-900">Username PPPoE</dt>
                                <dd class="text-right break-all">{{ $tagihan->pelanggan->username_pppoe }}</dd>
                            </div>
                            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
                                <dt class="font-semibold text-gray-900">No HP</dt>
                                <dd class="text-right">{{ $tagihan->pelanggan->no_hp }}</dd>
                            </div>
                            <div class="flex flex-col gap-1 sm:flex-row sm:justify-between">
                                <dt class="font-semibold text-gray-900">Alamat</dt>
                                <dd class="text-right">{{ $tagihan->pelanggan->alamat }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">
                        <h4 class="mb-4 text-lg font-semibold text-gray-900">Ringkasan Tagihan</h4>
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex items-center justify-between">
                                <span>Periode</span>
                                <span class="font-medium text-gray-900">{{ $tagihan->periode }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Paket</span>
                                <span class="font-medium text-gray-900">{{ $tagihan->pelanggan->paket->nama_paket ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Nominal</span>
                                <span class="font-medium text-gray-900">Rp {{ number_format($tagihan->nominal,0,',','.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Denda</span>
                                <span class="font-medium text-gray-900">Rp {{ number_format($tagihan->denda,0,',','.') }}</span>
                            </div>
                        </div>
                        <div class="mt-4 border-t border-blue-200 pt-4">
                            <div class="flex items-center justify-between text-lg font-bold text-blue-700">
                                <span>TOTAL</span>
                                <span>Rp {{ number_format($tagihan->total,0,',','.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('pembayaran.store') }}" method="POST" class="space-y-6">
                    @csrf

                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                            <p class="mb-2 font-semibold text-red-700">Terdapat beberapa masalah:</p>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">

                    <div class="rounded-xl border border-gray-200 p-5">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">Metode Pembayaran</label>
                                <select name="metode" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="Cash" {{ old('metode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="Transfer" {{ old('metode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                    <option value="QRIS" {{ old('metode') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">Biaya Admin</label>
                                <input type="number" name="biaya_admin" value="{{ old('biaya_admin', 0) }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">Uang Diterima</label>
                                <input id="dibayar" type="number" name="dibayar" value="{{ old('dibayar', $tagihan->total) }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">Kembalian</label>
                                <input id="kembalian" name="kembalian" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm">
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-1 block text-sm font-semibold text-gray-700">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center">
                        <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 font-semibold text-white transition hover:bg-green-700">
                            Simpan Pembayaran
                        </button>
                        <a href="{{ route('tagihan.show', $tagihan) }}" class="rounded-lg bg-gray-500 px-5 py-2.5 font-semibold text-white transition hover:bg-gray-600">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const nominal = {{ $tagihan->nominal }};
        const denda = {{ $tagihan->denda }};

        const admin = document.querySelector('[name="biaya_admin"]');
        const dibayar = document.getElementById('dibayar');
        const kembali = document.getElementById('kembalian');

        function hitung() {
            let biayaAdmin = parseFloat(admin.value) || 0;
            let bayar = parseFloat(dibayar.value) || 0;
            let total = nominal + denda + biayaAdmin;
            let hasil = bayar - total;
            kembali.value = hasil > 0 ? hasil : 0;
        }

        admin.addEventListener('keyup', hitung);
        admin.addEventListener('change', hitung);
        dibayar.addEventListener('keyup', hitung);
        dibayar.addEventListener('change', hitung);

        window.onload = hitung;
    </script>
</x-app-layout>