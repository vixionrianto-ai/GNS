<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Pembayaran Tagihan
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-4xl mx-auto">

            <div class="bg-white shadow rounded-lg p-6">

                <div class="mb-6">

                    <h3 class="text-2xl font-bold">

                        {{ $tagihan->invoice_no }}

                    </h3>

                    <p class="text-gray-500">

                        {{ $tagihan->pelanggan->nama }}

                    </p>

                </div>

                <form action="{{ route('pembayaran.store') }}"
                      method="POST">

                    @csrf

                    <input type="hidden"
                           name="tagihan_id"
                           value="{{ $tagihan->id }}">

                    <table class="table-auto w-full">

                        <tbody>

                            <tr>

                                <td class="py-2 font-semibold w-56">

                                    Periode

                                </td>

                                <td>

                                    {{ $tagihan->periode }}

                                </td>

                            </tr>

                            <tr>

                                <td class="py-2 font-semibold">

                                    Paket

                                </td>

                                <td>

                                    {{ $tagihan->pelanggan->paket->nama_paket }}

                                </td>

                            </tr>

                            <tr>

                                <td class="py-2 font-semibold">

                                    Nominal

                                </td>

                                <td>

                                    Rp {{ number_format($tagihan->nominal,0,',','.') }}

                                </td>

                            </tr>

                            <tr>

                                <td class="py-2 font-semibold">

                                    Denda

                                </td>

                                <td>

                                    Rp {{ number_format($tagihan->denda,0,',','.') }}

                                </td>

                            </tr>

                            <tr>

                                <td class="py-2 font-bold text-lg">

                                    TOTAL

                                </td>

                                <td class="font-bold text-blue-700 text-lg">

                                    Rp {{ number_format($tagihan->total,0,',','.') }}

                                </td>

                            </tr>

                        </tbody>

                    </table>

                    <hr class="my-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label class="font-semibold">

                                Metode Pembayaran

                            </label>

                            <select
                                name="metode"
                                class="w-full rounded border-gray-300">

                                <option value="Cash">

                                    Cash

                                </option>

                                <option value="Transfer">

                                    Transfer

                                </option>

                                <option value="QRIS">

                                    QRIS

                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="font-semibold">

                                Biaya Admin

                            </label>

                            <input

                                type="number"

                                name="biaya_admin"

                                value="0"

                                class="w-full rounded border-gray-300">

                        </div>

                        <div>

                            <label class="font-semibold">

                                Uang Diterima

                            </label>

                            <input

                                id="dibayar"

                                type="number"

                                name="dibayar"

                                required

                                class="w-full rounded border-gray-300">

                        </div>

                        <div>

                            <label class="font-semibold">

                                Kembalian

                            </label>

                            <input

                                id="kembalian"

                                readonly

                                class="w-full rounded bg-gray-100">

                        </div>

                    </div>

                    <div class="mt-5">

                        <label class="font-semibold">

                            Keterangan

                        </label>

                        <textarea

                            name="keterangan"

                            rows="3"

                            class="w-full rounded border-gray-300"></textarea>

                    </div>

                    <div class="mt-8 flex gap-3">

                        <button

                            class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">

                            Simpan Pembayaran

                        </button>

                        <a href="{{ route('tagihan.show',$tagihan) }}"

                           class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded">

                            Batal

                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

<script>

const total={{ $tagihan->total }};

const dibayar=document.getElementById('dibayar');

const kembali=document.getElementById('kembalian');

dibayar.addEventListener('keyup',function(){

let bayar=parseFloat(this.value)||0;

let hasil=bayar-total;

kembali.value=hasil>0?hasil:0;

});

</script>

</x-app-layout>