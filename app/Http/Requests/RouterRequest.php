<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'nama_router' => 'required|string|max:100',

            'lokasi' => 'nullable|string|max:255',

            'ip_router' => 'required|ip',

            'api_port' => 'required|integer|min:1|max:65535',

            'username' => 'required|string|max:100',

            'password' => 'required|string|max:255',

            'ssl' => 'boolean',

            'status' => 'required|string|max:20',

        ];
    }
}