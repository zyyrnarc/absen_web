<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermitRequest extends FormRequest
{
    public const TYPES = [
        'Sakit',
        'Izin',
        'Keperluan Kampus',
        'Acara Keluarga',
        'Lainnya',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(self::TYPES)],
            'permit_date' => ['nullable', 'date'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ];
    }
}
