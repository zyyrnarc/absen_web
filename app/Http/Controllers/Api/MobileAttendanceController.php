<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttendanceActionRequest;
use App\Models\Attendance;
use App\Models\Permit;
use App\Support\MobileApiAuth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MobileAttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->when($request->filled('month'), fn ($query) => $query->whereMonth('attendance_date', (int) $request->input('month')))
            ->when($request->filled('year'), fn ($query) => $query->whereYear('attendance_date', (int) $request->input('year')))
            ->latest('attendance_date')
            ->paginate(15);

        $attendances->setCollection(
            $attendances->getCollection()->map(fn (Attendance $attendance) => $this->transformAttendance($attendance))
        );

        return response()->json($attendances);
    }

    public function monthly(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $user->loadMissing('profile');

        $month = max(1, min(12, (int) $request->input('month', now()->month)));
        $year = (int) $request->input('year', now()->year);

        $monthData = $this->buildMonthData($user, $month, $year);
        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();

        return response()->json([
            'month' => [
                'number' => $month,
                'year' => $year,
                'label' => $monthData['month_start']->copy()->locale('en')->translatedFormat('F Y'),
                'start_date' => $monthData['month_start']->toDateString(),
                'end_date' => $monthData['month_end']->toDateString(),
                'previous' => [
                    'month' => $monthData['month_start']->copy()->subMonth()->month,
                    'year' => $monthData['month_start']->copy()->subMonth()->year,
                ],
                'next' => [
                    'month' => $monthData['month_start']->copy()->addMonth()->month,
                    'year' => $monthData['month_start']->copy()->addMonth()->year,
                ],
            ],
            'summary' => $monthData['calendar']['summary'],
            'calendar' => [
                'weekdays' => ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
                'start_weekday_index' => (int) $monthData['month_start']->dayOfWeek,
                'days' => $monthData['calendar']['days'],
                'legend' => [
                    ['key' => 'present', 'label' => 'Present', 'color' => 'green'],
                    ['key' => 'permit', 'label' => 'Permit', 'color' => 'orange'],
                    ['key' => 'absent', 'label' => 'Absent', 'color' => 'red'],
                ],
            ],
            'today_attendance' => $this->transformAttendance($todayAttendance),
            'actions' => [
                'can_check_in' => ! $todayAttendance?->check_in_at,
                'can_check_out' => (bool) $todayAttendance?->check_in_at && ! $todayAttendance?->check_out_at,
                'can_submit_permit' => true,
                'export_endpoint' => url('/api/mobile/attendances/monthly/export?month='.$month.'&year='.$year),
            ],
        ]);
    }

    public function monthlyExport(Request $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $user->loadMissing('profile');

        $month = max(1, min(12, (int) $request->input('month', now()->month)));
        $year = (int) $request->input('year', now()->year);

        $monthData = $this->buildMonthData($user, $month, $year);

        return response()->json([
            'export' => [
                'type' => 'monthly-absence',
                'title' => 'Monthly Absence Report',
                'filename' => 'monthly-absence-'.$user->id.'-'.$monthData['month_start']->format('Y-m').'.pdf',
                'generated_at' => now()->toDateTimeString(),
                'student' => [
                    'name' => $user->name,
                    'student_id' => $user->profile?->student_id,
                    'institution_name' => $user->profile?->institution_name,
                    'major' => $user->profile?->major,
                ],
                'period' => [
                    'label' => $monthData['month_start']->copy()->locale('en')->translatedFormat('F Y'),
                    'start_date' => $monthData['month_start']->toDateString(),
                    'end_date' => $monthData['month_end']->toDateString(),
                ],
                'summary' => $monthData['calendar']['summary'],
                'calendar' => $monthData['calendar']['days'],
                'notes' => 'Payload ini disiapkan dari backend agar aplikasi mobile bisa langsung generate PDF.',
            ],
        ]);
    }

    public function checkIn(StoreAttendanceActionRequest $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $attendance = Attendance::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => today()->toDateString(),
            ],
            [
                'status' => 'present',
            ]
        );

        if ($attendance->check_in_at) {
            return response()->json([
                'message' => 'Absen masuk hari ini sudah tercatat.',
                'data' => $attendance,
            ], 422);
        }

        $attendance->fill([
            'check_in_at' => now(),
            'check_in_latitude' => $request->input('latitude'),
            'check_in_longitude' => $request->input('longitude'),
            'notes' => $request->input('notes'),
        ])->save();

        return response()->json([
            'message' => 'Absen masuk berhasil disimpan.',
            'data' => $this->transformAttendance($attendance->fresh()),
        ]);
    }

    public function checkOut(StoreAttendanceActionRequest $request): JsonResponse
    {
        $user = MobileApiAuth::userFromRequest($request);

        if (! $user) {
            return response()->json([
                'message' => 'Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            return response()->json([
                'message' => 'Absen masuk hari ini belum ada.',
            ], 422);
        }

        if ($attendance->check_out_at) {
            return response()->json([
                'message' => 'Absen pulang hari ini sudah tercatat.',
                'data' => $attendance,
            ], 422);
        }

        $attendance->fill([
            'check_out_at' => now(),
            'check_out_latitude' => $request->input('latitude'),
            'check_out_longitude' => $request->input('longitude'),
            'notes' => $request->filled('notes') ? $request->input('notes') : $attendance->notes,
        ])->save();

        return response()->json([
            'message' => 'Absen pulang berhasil disimpan.',
            'data' => $this->transformAttendance($attendance->fresh()),
        ]);
    }

    private function buildMonthlyCalendar($user, Carbon $monthStart, Carbon $monthEnd, Collection $attendances, Collection $permits): array
    {
        $days = [];
        $summary = [
            'present' => 0,
            'permit' => 0,
            'absent' => 0,
            'workdays' => 0,
        ];

        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $date = $cursor->toDateString();
            $attendance = $attendances->get($date);
            $permit = $permits->get($date);

            $isWeekend = $cursor->isWeekend();
            $isFuture = $cursor->gt(today());
            $isWithinInternship = $this->isWithinInternshipPeriod($user, $cursor);

            $status = null;

            if ($attendance?->check_in_at) {
                $status = 'present';
                $summary['present']++;
            } elseif ($permit) {
                $status = 'permit';
                $summary['permit']++;
            } elseif (! $isWeekend && ! $isFuture && $isWithinInternship) {
                $status = 'absent';
                $summary['absent']++;
            }

            if (! $isWeekend && $isWithinInternship && ! $isFuture) {
                $summary['workdays']++;
            }

            $days[] = [
                'date' => $date,
                'day' => (int) $cursor->day,
                'weekday' => $cursor->copy()->locale('en')->translatedFormat('D'),
                'status' => $status,
                'is_today' => $cursor->isToday(),
                'is_future' => $isFuture,
                'is_weekend' => $isWeekend,
                'check_in_time' => $attendance?->check_in_at?->format('H:i'),
                'check_out_time' => $attendance?->check_out_at?->format('H:i'),
                'permit_type' => $permit?->type,
                'permit_status' => $permit?->status,
            ];

            $cursor->addDay();
        }

        return [
            'summary' => $summary,
            'days' => $days,
        ];
    }

    private function buildMonthData($user, int $month, int $year): array
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $attendances = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $attendance) => $attendance->attendance_date->toDateString());

        $permits = Permit::query()
            ->where('user_id', $user->id)
            ->whereBetween('permit_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(fn (Permit $permit) => $permit->permit_date->toDateString());

        return [
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'calendar' => $this->buildMonthlyCalendar($user, $monthStart, $monthEnd, $attendances, $permits),
        ];
    }

    private function isWithinInternshipPeriod($user, Carbon $date): bool
    {
        $start = $user->profile?->internship_start;
        $end = $user->profile?->internship_end;

        if ($start && $date->lt($start)) {
            return false;
        }

        if ($end && $date->gt($end)) {
            return false;
        }

        return true;
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
