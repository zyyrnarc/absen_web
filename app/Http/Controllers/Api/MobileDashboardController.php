<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\InternActivity;
use App\Models\Permit;
use App\Models\User;
use App\Support\MobileApiAuth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $user->loadMissing('profile');

        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();

        $todayActivities = InternActivity::query()
            ->where('user_id', $user->id)
            ->whereDate('activity_date', today())
            ->count();

        $presentCount = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->whereNotNull('check_in_at')
            ->count();

        $permitCount = Permit::query()
            ->where('user_id', $user->id)
            ->whereBetween('permit_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->count();

        $recentActivities = InternActivity::query()
            ->where('user_id', $user->id)
            ->latest('activity_date')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn (InternActivity $activity) => [
                'id' => $activity->id,
                'date' => optional($activity->activity_date)->toDateString(),
                'title' => $activity->title,
                'description' => $activity->description,
                'status' => $activity->status,
            ])
            ->values();

        $absentCount = max(
            $this->countActiveWorkdaysInMonth($user) - $presentCount - $permitCount,
            0
        );

        return response()->json([
            'current_date' => [
                'iso' => today()->toDateString(),
                'label' => now()->translatedFormat('l, F j, Y'),
            ],
            'date' => today()->toDateString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'initial' => Str::upper(Str::substr($user->name, 0, 1)),
                'email' => $user->email,
                'username' => $user->username,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'profile' => [
                    'student_id' => $user->profile?->student_id,
                    'institution_name' => $user->profile?->institution_name,
                    'major' => $user->profile?->major,
                    'study_program' => $user->profile?->study_program,
                    'division' => $user->profile?->division,
                    'supervisor_name' => $user->profile?->supervisor_name,
                    'status' => $user->profile?->status,
                ],
            ],
            'attendance_today' => $this->transformAttendance($todayAttendance),
            'today_activity_count' => $todayActivities,
            'monthly_attendance_count' => $presentCount,
            'monthly_statistics' => [
                'present' => $presentCount,
                'permit' => $permitCount,
                'absent' => $absentCount,
                'workdays' => $this->countActiveWorkdaysInMonth($user),
            ],
            'quick_actions' => [
                'can_check_in' => ! $todayAttendance?->check_in_at,
                'can_check_out' => (bool) $todayAttendance?->check_in_at && ! $todayAttendance?->check_out_at,
                'can_submit_permit' => true,
                'can_submit_activity' => true,
            ],
            'recent_activities' => $recentActivities,
        ]);
    }

    private function countActiveWorkdaysInMonth(User $user): int
    {
        $start = now()->startOfMonth();
        $end = now();

        if ($user->profile?->internship_start instanceof Carbon && $user->profile->internship_start->greaterThan($start)) {
            $start = $user->profile->internship_start->copy();
        }

        if ($user->profile?->internship_end instanceof Carbon && $user->profile->internship_end->lessThan($end)) {
            $end = $user->profile->internship_end->copy();
        }

        if ($end->lt($start)) {
            return 0;
        }

        $workdays = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $workdays++;
            }

            $cursor->addDay();
        }

        return $workdays;
    }

    private function transformAttendance(?Attendance $attendance): ?array
    {
        if (! $attendance) {
            return null;
        }

        return [
            'id' => $attendance->id,
            'attendance_date' => optional($attendance->attendance_date)->toDateString(),
            'status' => $attendance->status,
            'check_in_at' => optional($attendance->check_in_at)->toDateTimeString(),
            'check_out_at' => optional($attendance->check_out_at)->toDateTimeString(),
            'check_in_time' => optional($attendance->check_in_at)->format('H:i'),
            'check_out_time' => optional($attendance->check_out_at)->format('H:i'),
            'notes' => $attendance->notes,
        ];
    }
}
