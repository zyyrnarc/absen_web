<?php

namespace App\Http\Requests\Api;

use App\Support\MobileApiAuth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMobileProfileRequest extends FormRequest
{
    public const GENDER_OPTIONS = [
        'Male',
        'Female',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = MobileApiAuth::userFromRequest($this);
        $profileId = $user?->profile?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'student_id' => ['required', 'string', 'max:255', Rule::unique('intern_profiles', 'student_id')->ignore($profileId)],
            'study_program' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'institution_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(self::GENDER_OPTIONS)],
            'division' => ['nullable', 'string', 'max:255'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
