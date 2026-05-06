<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\InternActivity;
use App\Models\User;
use Carbon\Carbon;

class WeeklyActivityController extends Controller
{
    public function index()
    {
        return view('admin.weekly-activity.index', $this->buildWeeklyActivityData(
            (int) request('month', now()->month),
            (int) request('year', now()->year),
            request('student_id')
        ));
    }

    public function export()
    {
        $setting = AppSetting::query()->first();

        return view('admin.weekly-activity.export', array_merge(
            $this->buildWeeklyActivityData(
                (int) request('month', now()->month),
                (int) request('year', now()->year),
                request('student_id')
            ),
            [
                'setting' => $setting,
                'generatedAt' => now(),
            ]
        ));
    }

    private function formatActivityTimes(InternActivity $activity): string
    {
        $checkIn = $activity->attendance?->check_in_at?->format('H:i');
        $checkOut = $activity->attendance?->check_out_at?->format('H:i');

        if ($checkIn && $checkOut) {
            return "{$checkIn} - {$checkOut}";
        }

        if ($checkIn) {
            return "{$checkIn} - Pending";
        }

        return '-';
    }

    private function buildWeeklyActivityData(int $selectedMonth, int $selectedYear, $selectedStudent): array
    {
        $students = User::query()
            ->where('role', 'intern')
            ->orderBy('name')
            ->get(['id', 'name']);

        $activities = InternActivity::query()
            ->with(['user', 'attendance'])
            ->whereYear('activity_date', $selectedYear)
            ->whereMonth('activity_date', $selectedMonth)
            ->when($selectedStudent, fn ($query) => $query->where('user_id', $selectedStudent))
            ->latest('activity_date')
            ->latest('id')
            ->get()
            ->map(fn ($activity) => [
                'id' => $activity->id,
                'name' => $activity->user?->name ?? 'Pengguna',
                'day' => Carbon::parse($activity->activity_date)->locale('en')->dayName,
                'times' => $this->formatActivityTimes($activity),
                'activity' => $activity->title,
            ]);

        $selectedStudentName = $students->firstWhere('id', (int) $selectedStudent)?->name;

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return compact(
            'activities',
            'students',
            'months',
            'selectedMonth',
            'selectedYear',
            'selectedStudent',
            'selectedStudentName'
        );
    }
}
