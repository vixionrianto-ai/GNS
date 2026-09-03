<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'tagihan_id' => [
                'required',
                'integer',
                'exists:tagihans,id',
            ],
            'metode' => [
                'required',
                'string',
                'max:30',
            ],
            'dibayar' => [
                'required',
                'numeric',
                'min:1',
            ],
            'biaya_admin' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tagihan_id.required' => 'Tagihan wajib dipilih.',
            'tagihan_id.integer' => 'Tagihan tidak valid.',
            'tagihan_id.exists' => 'Tagihan tidak ditemukan.',
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'dibayar.required' => 'Nominal pembayaran wajib diisi.',
            'dibayar.numeric' => 'Nominal pembayaran harus berupa angka.',
            'dibayar.min' => 'Nominal pembayaran tidak valid.',
        ];
    }
}
