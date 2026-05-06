<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Attendance;
use App\Models\Permit;
use Carbon\Carbon;

class MonthlyAbsenceController extends Controller
{
    public function index()
    {
        $selectedMonth = (int) request('month', now()->month);
        $selectedYear  = (int) request('year', now()->year);
        $search        = (string) request('search', '');

        return view('admin.monthly-absence.index', $this->buildMonthlyAbsenceData(
            $selectedMonth,
            $selectedYear,
            $search
        ));
    }

    public function export()
    {
        $selectedMonth = (int) request('month', now()->month);
        $selectedYear = (int) request('year', now()->year);
        $search = (string) request('search', '');
        $setting = AppSetting::query()->first();

        return view('admin.monthly-absence.export', array_merge(
            $this->buildMonthlyAbsenceData($selectedMonth, $selectedYear, $search),
            [
                'setting' => $setting,
                'generatedAt' => now(),
            ]
        ));
    }

    public function approvePermit(int $id)
    {
        Permit::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', "Permit berhasil di-approve.");
    }

    private function countWorkdays(int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();
        $count = 0;
        while ($start->lte($end)) {
            if ($start->isWeekday()) $count++;
            $start->addDay();
        }
        return $count;
    }

    private function buildMonthlyAbsenceData(int $selectedMonth, int $selectedYear, string $search): array
    {
        $totalWorkdays = $this->countWorkdays($selectedYear, $selectedMonth);
        $presentToday = Attendance::whereDate('attendance_date', today())
            ->where(function ($query) {
                $query->whereNotNull('check_in_at')
                    ->orWhereNotNull('time');
            })
            ->count();
        $waitingPermit = Permit::where('status', 'pending')->count();

        $attendances = Attendance::with(['user', 'student'])
            ->whereYear('attendance_date', $selectedYear)
            ->whereMonth('attendance_date', $selectedMonth)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('attendance_date')
            ->get();

        $pendingPermits = Permit::where('status', 'pending')
            ->with(['user', 'student'])
            ->latest('permit_date')
            ->take(5)
            ->get()
            ->map(fn ($permit) => [
                'id' => $permit->id,
                'name' => $permit->user?->name ?? $permit->student?->name ?? 'Pengguna',
                'type' => $permit->type,
                'date' => Carbon::parse($permit->permit_date)->locale('id')->isoFormat('D MMMM YYYY'),
            ])
            ->toArray();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return compact(
            'totalWorkdays',
            'presentToday',
            'waitingPermit',
            'attendances',
            'pendingPermits',
            'months',
            'selectedMonth',
            'selectedYear',
            'search'
        );
    }
}
