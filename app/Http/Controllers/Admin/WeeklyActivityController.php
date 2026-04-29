<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternActivity;
use App\Models\User;
use Carbon\Carbon;

class WeeklyActivityController extends Controller
{
    public function index()
    {
        $selectedMonth  = request('month', now()->month);
        $selectedYear   = request('year', now()->year);
        $selectedStudent = request('student_id');

        $students = User::query()
            ->where('role', 'intern')
            ->orderBy('name')
            ->get(['id', 'name']);

        $activities = InternActivity::query()
            ->with(['user', 'attendance'])
            ->whereYear('activity_date', $selectedYear)
            ->whereMonth('activity_date', $selectedMonth)
            ->when($selectedStudent, fn ($q) => $q->where('user_id', $selectedStudent))
            ->latest('activity_date')
            ->latest('id')
            ->get()
            ->map(fn ($a) => [
                'id'       => $a->id,
                'name'     => $a->user?->name ?? 'Pengguna',
                'day'      => Carbon::parse($a->activity_date)->locale('en')->dayName,
                'times'    => $this->formatActivityTimes($a),
                'activity' => $a->title,
            ]);

        $months = [
            1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
            5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November', 12=>'Desember',
        ];

        return view('admin.weekly-activity.index', compact(
            'activities','students','months',
            'selectedMonth','selectedYear','selectedStudent'
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
}
