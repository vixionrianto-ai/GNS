<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
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
                'max:50',
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
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'tagihan_id.required' => 'Tagihan wajib dipilih.',
            'tagihan_id.exists'   => 'Tagihan tidak ditemukan.',

            'metode.required'     => 'Metode pembayaran wajib diisi.',

            'dibayar.required'    => 'Nominal pembayaran wajib diisi.',
            'dibayar.numeric'     => 'Nominal pembayaran harus berupa angka.',
            'dibayar.min'         => 'Nominal pembayaran minimal 1.',

            'biaya_admin.numeric' => 'Biaya admin harus berupa angka.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(

            response()->json([

                'success' => false,

                'message' => 'Validasi gagal.',

                'errors' => $validator->errors(),

            ], 422)

        );
    }
}