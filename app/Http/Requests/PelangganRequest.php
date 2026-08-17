<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PelangganRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'kode_pelanggan' => [
                'nullable',
                'string',
                'max:50',
                'unique:pelanggans,kode_pelanggan,' . $id,
            ],
            'nama' => [
                'required',
                'string',
                'max:100',
            ],
            'alamat' => [
                'nullable',
                'string',
            ],
            'no_hp' => [
                'required',
                'string',
                'max:20',
            ],
            'router_id' => [
                'required',
                'exists:routers,id',
            ],
            'paket_id' => [
                'required',
                'exists:pakets,id',
            ],
            'username_pppoe' => [
                'nullable',
                'string',
                'max:100',
                'unique:pelanggans,username_pppoe,' . $id,
            ],
            'password_pppoe' => [
                'nullable',
                'string',
                'max:100',
            ],
            'ip_address' => [
                'nullable',
                'string',
            ],
            'mac_address' => [
                'nullable',
                'string',
                'max:30',
            ],
            'tanggal_pasang' => [
                'nullable',
                'string',
            ],
            'tanggal_aktif' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:Aktif,Isolir,Nonaktif',
            ],
            'keterangan' => [
                'nullable',
                'string',
            ],
            'isolation_use_default' => [
                'nullable',
                'boolean',
            ],
            'isolation_period_limit' => [
                'nullable',
                'integer',
            ],
        ];
    }
}
