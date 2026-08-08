<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiGenerateTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => [
                'nullable',
                'date',
            ],
        ];
    }
}