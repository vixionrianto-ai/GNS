<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PembayaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            'tagihan_id' => [
                'required',
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

    /**
     * Custom Message
     */
    public function messages(): array
    {
        return [

            'tagihan_id.required' =>
                'Tagihan wajib dipilih.',

            'tagihan_id.exists' =>
                'Tagihan tidak ditemukan.',

            'metode.required' =>
                'Metode pembayaran wajib dipilih.',

            'dibayar.required' =>
                'Nominal pembayaran wajib diisi.',

            'dibayar.numeric' =>
                'Nominal pembayaran harus berupa angka.',

            'dibayar.min' =>
                'Nominal pembayaran tidak valid.',

        ];
    }
}