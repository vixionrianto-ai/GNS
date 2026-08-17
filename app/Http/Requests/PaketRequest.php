<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'router_id' => 'required|exists:routers,id',
            'nama_paket' => 'required|string|max:100',
            'kecepatan' => 'nullable|string|max:50',
            'profile_mikrotik' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:Aktif,Nonaktif',
            'keterangan' => 'nullable|string',
        ];
    }
}
