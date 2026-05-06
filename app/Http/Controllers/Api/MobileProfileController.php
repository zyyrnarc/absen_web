<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateMobilePasswordRequest;
use App\Http\Requests\Api\UpdateMobileProfileRequest;
use App\Models\User;
use App\Support\MobileApiAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MobileProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $user->loadMissing('profile');

        return response()->json([
            'user' => $this->transformUser($user),
            'options' => [
                'gender' => collect(UpdateMobileProfileRequest::GENDER_OPTIONS)
                    ->map(fn (string $value) => ['value' => $value, 'label' => $value])
                    ->values(),
            ],
            'actions' => [
                'can_edit_profile' => true,
                'can_reset_password' => true,
                'logout_endpoint' => url('/api/mobile/logout'),
            ],
        ]);
    }

    public function update(UpdateMobileProfileRequest $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $user->loadMissing('profile');
        $validated = $request->validated();

        DB::transaction(function () use ($request, $user, $validated): void {
            $userPayload = [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? $user->phone,
            ];

            if ($request->hasFile('avatar')) {
                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }

                $userPayload['avatar_path'] = $request->file('avatar')->store('mobile-avatars', 'public');
            }

            $user->update($userPayload);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id' => $validated['student_id'],
                    'study_program' => $validated['study_program'],
                    'major' => $validated['major'],
                    'institution_name' => $validated['institution_name'],
                    'gender' => $validated['gender'],
                    'division' => $validated['division'] ?? $user->profile?->division,
                    'supervisor_name' => $validated['supervisor_name'] ?? $user->profile?->supervisor_name,
                    'status' => $user->profile?->status ?? 'active',
                    'internship_start' => $user->profile?->internship_start,
                    'internship_end' => $user->profile?->internship_end,
                ]
            );
        });

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $this->transformUser($user->fresh()->load('profile')),
        ]);
    }

    public function updatePassword(UpdateMobilePasswordRequest $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai.',
                'errors' => [
                    'current_password' => ['Password saat ini tidak sesuai.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => $request->input('password'),
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

    private function transformUser(User $user): array
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
            'profile' => [
                'student_id' => $user->profile?->student_id,
                'study_program' => $user->profile?->study_program,
                'major' => $user->profile?->major,
                'institution_name' => $user->profile?->institution_name,
                'gender' => $user->profile?->gender,
                'division' => $user->profile?->division,
                'supervisor_name' => $user->profile?->supervisor_name,
                'status' => $user->profile?->status,
            ],
            'badges' => [
                'student_id' => $user->profile?->student_id,
                'study_program' => $user->profile?->study_program,
            ],
            'personal_information' => [
                'email' => $user->email,
                'study_program' => $user->profile?->study_program,
                'major' => $user->profile?->major,
                'campus' => $user->profile?->institution_name,
                'gender' => $user->profile?->gender,
            ],
        ];
    }
}
