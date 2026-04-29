<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // Stat cards
        $totalWorkdays   = $this->countWorkdays($selectedYear, $selectedMonth);
        $presentToday    = Attendance::whereDate('attendance_date', today())
                            ->where(function ($query) {
                                $query->whereNotNull('check_in_at')
                                    ->orWhereNotNull('time');
                            })->count();
        $waitingPermit   = Permit::where('status', 'pending')->count();

        // Tabel daily attendance
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

        // Pending permits (sidebar kanan)
        $pendingPermits = Permit::where('status', 'pending')
            ->with(['user', 'student'])->latest('permit_date')->take(5)->get()
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->user?->name ?? $p->student?->name ?? 'Pengguna',
                'type' => $p->type,
                'date' => Carbon::parse($p->permit_date)->locale('id')->isoFormat('D MMMM YYYY'),
            ])->toArray();

        $months = [
            1=>'Januari', 2=>'Februari', 3=>'Maret',    4=>'April',
            5=>'Mei',     6=>'Juni',     7=>'Juli',      8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November', 12=>'Desember',
        ];

        return view('admin.monthly-absence.index', compact(
            'totalWorkdays','presentToday','waitingPermit',
            'attendances','pendingPermits','months',
            'selectedMonth','selectedYear','search'
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
}
