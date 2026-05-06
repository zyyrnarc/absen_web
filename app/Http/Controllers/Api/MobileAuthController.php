<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MobileLoginRequest;
use App\Models\InternProfile;
use App\Models\User;
use App\Support\MobileApiAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileAuthController extends Controller
{
    public function login(MobileLoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Email atau password tidak sesuai.',
            ], 401);
        }

        if ($user->role !== 'intern') {
            return response()->json([
                'message' => 'Akun ini tidak dapat mengakses aplikasi mobile.',
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun tidak aktif. Hubungi admin.',
            ], 403);
        }

        $profile = InternProfile::query()->where('user_id', $user->id)->first();
        $token = MobileApiAuth::issueToken($user);

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $this->transformUser($user, $profile),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $profile = InternProfile::query()->where('user_id', $user->id)->first();

        return response()->json([
            'user' => $this->transformUser($user, $profile),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        MobileApiAuth::revokeCurrentToken($request);

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function transformUser(User $user, ?InternProfile $profile): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'initial' => Str::upper(Str::substr($user->name, 0, 1)),
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null,
            'profile' => $profile ? [
                'student_id' => $profile->student_id,
                'institution_name' => $profile->institution_name,
                'major' => $profile->major,
                'study_program' => $profile->study_program,
                'division' => $profile->division,
                'supervisor_name' => $profile->supervisor_name,
                'gender' => $profile->gender,
                'internship_start' => optional($profile->internship_start)->toDateString(),
                'internship_end' => optional($profile->internship_end)->toDateString(),
                'status' => $profile->status,
            ] : null,
        ];
    }
}
