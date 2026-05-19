<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInternActivityRequest;
use App\Http\Requests\Api\UpdateInternActivityRequest;
use App\Models\AppSetting;
use App\Models\Attendance;
use App\Models\InternActivity;
use App\Models\Mentor;
use App\Support\MobileApiAuth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $activities = InternActivity::query()
            ->with('attendance')
            ->where('user_id', $user->id)
            ->when($request->filled('month'), fn ($query) => $query->whereMonth('activity_date', (int) $request->input('month')))
            ->when($request->filled('year'), fn ($query) => $query->whereYear('activity_date', (int) $request->input('year')))
            ->latest('activity_date')
            ->latest('id')
            ->paginate(15);

        $activities->setCollection(
            $activities->getCollection()->map(fn (InternActivity $activity) => $this->transformActivity($activity))
        );

        return response()->json($activities);
    }

    public function weekly(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $referenceDate = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : today();

        $weekStart = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $referenceDate->copy()->endOfWeek(Carbon::FRIDAY);

        $activities = InternActivity::query()
            ->with('attendance')
            ->where('user_id', $user->id)
            ->whereBetween('activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->latest('activity_date')
            ->latest('id')
            ->get()
            ->map(fn (InternActivity $activity) => $this->transformActivity($activity))
            ->values();

        return response()->json([
            'week' => [
                'reference_date' => $referenceDate->toDateString(),
                'start_date' => $weekStart->toDateString(),
                'end_date' => $weekEnd->toDateString(),
                'label' => $weekStart->locale('en')->translatedFormat('F j, Y').' - '.$weekEnd->locale('en')->translatedFormat('F j, Y'),
                'previous_start_date' => $weekStart->copy()->subWeek()->toDateString(),
                'next_start_date' => $weekStart->copy()->addWeek()->toDateString(),
            ],
            'summary' => [
                'count' => $activities->count(),
            ],
            'actions' => [
                'can_create' => true,
                'export_endpoint' => url('/api/mobile/activities/weekly/export?date='.$referenceDate->toDateString()),
            ],
            'items' => $activities,
        ]);
    }

    public function weeklyExport(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $referenceDate = $request->filled('date')
            ? Carbon::parse($request->input('date'))
            : today();

        $weekStart = $referenceDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $referenceDate->copy()->endOfWeek(Carbon::FRIDAY);
        $profile = $user->profile;
        $setting = AppSetting::query()->first();
        $industryName = $user->industry_name
            ?: ($setting?->company_name ?: ($profile?->division ?: '-'));
        [$mentorName, $mentorPosition] = $this->signatureSupervisor(
            $user->mentor_name ?: $profile?->supervisor_name,
            $user->mentor_position
        );
        $weekNumber = $this->reportWeekNumber(
            $user->id,
            $profile?->internship_start,
            $weekStart
        );

        $items = InternActivity::query()
            ->with('attendance')
            ->where('user_id', $user->id)
            ->whereBetween('activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->latest('activity_date')
            ->latest('id')
            ->get()
            ->map(fn (InternActivity $activity) => $this->transformActivity($activity))
            ->values();

        return response()->json([
            'export' => [
                'type' => 'weekly-activity',
                'title' => 'Weekly Activity Report',
                'filename' => 'weekly-activity-'.$user->id.'-'.$weekStart->format('Y-m-d').'.pdf',
                'generated_at' => now()->toDateTimeString(),
                'student' => [
                    'nim' => $user->nim ?: ($profile?->student_id ?: '-'),
                    'name' => $user->name ?: '-',
                    'email' => $user->email ?: '-',
                    'department' => $user->department ?: ($profile?->major ?: '-'),
                    'study_program' => $user->study_program ?: ($profile?->study_program ?: '-'),
                ],

                'industry' => [
                    'name' => $industryName,
                ],

                'mentor' => [
                    'name' => $mentorName,
                    'position' => $mentorPosition,
                ],
                'period' => [
                    'week_number' => $weekNumber,
                    'start_date' => $weekStart->toDateString(),
                    'end_date' => $weekEnd->toDateString(),
                    'label' => $weekStart->locale('en')->translatedFormat('F j, Y').' - '.$weekEnd->locale('en')->translatedFormat('F j, Y'),
                ],
                'items' => $items,
                'notes' => 'Payload ini disiapkan dari backend agar aplikasi mobile bisa langsung generate PDF.',
            ],
        ]);
    }

    public function store(StoreInternActivityRequest $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $attendanceId = $request->input('attendance_id');

        if ($attendanceId && ! $this->ownsAttendance($user->id, (int) $attendanceId)) {
            return response()->json([
                'message' => 'Absensi yang dipilih tidak valid.',
            ], 422);
        }

        $activity = InternActivity::query()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'activity_date' => $request->input('activity_date', today()->toDateString()),
            'start_time' => $request->filled('start_time') ? $request->input('start_time') : '09:00',
            'end_time' => $request->filled('end_time') ? $request->input('end_time') : '16:00',
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $request->input('status', 'submitted'),
        ]);

        return response()->json([
            'message' => 'Aktivitas berhasil disimpan.',
            'data' => $this->transformActivity($activity->load('attendance')),
        ], 201);
    }

    public function update(UpdateInternActivityRequest $request, int $activity): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $activityModel = InternActivity::query()
            ->where('user_id', $user->id)
            ->findOrFail($activity);

        $attendanceId = $request->input('attendance_id');

        if ($attendanceId && ! $this->ownsAttendance($user->id, (int) $attendanceId)) {
            return response()->json([
                'message' => 'Absensi yang dipilih tidak valid.',
            ], 422);
        }

        $data = $request->validated();

        if (! $request->filled('start_time') && ! $activityModel->start_time) {
            $data['start_time'] = '09:00';
        }

        if (! $request->filled('end_time') && ! $activityModel->end_time) {
            $data['end_time'] = '16:00';
        }

        $activityModel->fill($data)->save();

        return response()->json([
            'message' => 'Aktivitas berhasil diperbarui.',
            'data' => $this->transformActivity($activityModel->fresh()->load('attendance')),
        ]);
    }

    public function destroy(Request $request, int $activity): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $activityModel = InternActivity::query()
            ->where('user_id', $user->id)
            ->findOrFail($activity);

        $activityModel->delete();

        return response()->json([
            'message' => 'Aktivitas berhasil dihapus.',
        ]);
    }

    private function ownsAttendance(int $userId, int $attendanceId): bool
    {
        return Attendance::query()
            ->where('user_id', $userId)
            ->whereKey($attendanceId)
            ->exists();
    }

    private function transformActivity(InternActivity $activity): array
    {
        $startTime = $this->formatStoredTime($activity->start_time);
        $endTime = $this->formatStoredTime($activity->end_time);

        if (! $activity->attendance_id) {
            $startTime ??= '09:00';
            $endTime ??= '16:00';
        }

        return [
            'id' => $activity->id,
            'attendance_id' => $activity->attendance_id,
            'activity_date' => optional($activity->activity_date)->toDateString(),
            'activity_day' => $activity->activity_date?->locale('en')->translatedFormat('l') ?? '-',
            'start_time' => $startTime ?? '-',
            'end_time' => $endTime ?? '-',
            'title' => $activity->title ?? '-',
            'description' => $activity->description ?? '-',
            'status' => $activity->status ?? '-',
            'times' => $this->formatTimes($activity),
            'can_edit' => true,
            'can_delete' => true,
            'created_at' => optional($activity->created_at)->toDateTimeString(),
            'updated_at' => optional($activity->updated_at)->toDateTimeString(),
        ];
    }

    private function formatTimes(InternActivity $activity): ?string
    {
        $startTime = $this->formatStoredTime($activity->start_time);
        $endTime = $this->formatStoredTime($activity->end_time);

        if ($startTime && $endTime) {
            return "{$startTime} - {$endTime}";
        }

        if ($startTime) {
            return "{$startTime} - Pending";
        }

        $checkIn = $activity->attendance?->check_in_at?->format('H:i');
        $checkOut = $activity->attendance?->check_out_at?->format('H:i');

        if ($checkIn && $checkOut) {
            return "{$checkIn} - {$checkOut}";
        }

        if ($checkIn) {
            return "{$checkIn} - Pending";
        }

        if (! $activity->attendance_id) {
            return '09:00 - 16:00';
        }

        return null;
    }

    private function formatStoredTime($time): ?string
    {
        if (! $time) {
            return null;
        }

        return Carbon::parse($time)->format('H:i');
    }

    private function signatureSupervisor(?string $supervisorName, ?string $fallbackPosition = null): array
    {
        $name = trim((string) $supervisorName);
        $mentor = $name !== ''
            ? Mentor::query()->where('name', $name)->first()
            : null;

        if (! $mentor && $name !== '') {
            $mentor = Mentor::query()
                ->where('name', 'like', '%'.$name.'%')
                ->first();
        }

        $baseName = trim(Str::before($name, ','));
        if (! $mentor && $baseName !== '' && $baseName !== $name) {
            $mentor = Mentor::query()
                ->where('name', 'like', '%'.$baseName.'%')
                ->first();
        }

        $position = trim((string) ($mentor?->position ?: $fallbackPosition));
        if (Str::lower($position) === 'pembimbing industri') {
            $position = '';
        }

        return [
            $mentor?->name ?: ($name !== '' ? $name : '-'),
            $position !== '' ? $position : '-',
        ];
    }

    private function reportWeekNumber(int $userId, $internshipStart, Carbon $weekStart): int
    {
        $startDate = $internshipStart ? Carbon::parse($internshipStart) : null;

        if (! $startDate) {
            $firstActivityDate = InternActivity::query()
                ->where('user_id', $userId)
                ->min('activity_date');

            if ($firstActivityDate) {
                $startDate = Carbon::parse($firstActivityDate);
            }
        }

        if (! $startDate) {
            return 1;
        }

        $firstWeekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);

        if ($weekStart->lessThan($firstWeekStart)) {
            return 1;
        }

        return ((int) $firstWeekStart->diffInWeeks($weekStart)) + 1;
    }
}
