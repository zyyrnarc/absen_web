<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => ['nullable', 'integer', 'exists:attendances,id'],
            'activity_date' => ['nullable', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['nullable', 'in:draft,submitted'],
        ];
    }
}
