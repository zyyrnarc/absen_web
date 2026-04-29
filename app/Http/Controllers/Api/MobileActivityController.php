<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInternActivityRequest;
use App\Http\Requests\Api\UpdateInternActivityRequest;
use App\Models\Attendance;
use App\Models\InternActivity;
use App\Support\MobileApiAuth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'period' => [
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

        $activityModel->fill($request->validated())->save();

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
        return [
            'id' => $activity->id,
            'attendance_id' => $activity->attendance_id,
            'activity_date' => optional($activity->activity_date)->toDateString(),
            'activity_day' => $activity->activity_date?->locale('en')->translatedFormat('l'),
            'title' => $activity->title,
            'description' => $activity->description,
            'status' => $activity->status,
            'times' => $this->formatTimes($activity),
            'can_edit' => true,
            'can_delete' => true,
            'created_at' => optional($activity->created_at)->toDateTimeString(),
            'updated_at' => optional($activity->updated_at)->toDateTimeString(),
        ];
    }

    private function formatTimes(InternActivity $activity): ?string
    {
        $checkIn = $activity->attendance?->check_in_at?->format('H:i');
        $checkOut = $activity->attendance?->check_out_at?->format('H:i');

        if ($checkIn && $checkOut) {
            return "{$checkIn} - {$checkOut}";
        }

        if ($checkIn) {
            return "{$checkIn} - Pending";
        }

        return null;
    }
}
