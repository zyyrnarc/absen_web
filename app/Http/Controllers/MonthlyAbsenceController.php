<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Permit;
use App\Models\Student;
use Carbon\Carbon;

class MonthlyAbsenceController extends Controller
{
    public function index()
    {
        $selectedMonth = request('month', now()->month);
        $selectedYear  = request('year', now()->year);
        $search        = request('search');

        // Stat cards
        $totalWorkdays   = $this->countWorkdays($selectedYear, $selectedMonth);
        $presentToday    = Attendance::whereDate('attendance_date', today())
                            ->where('status', 'hadir')->count();
        $waitingPermit   = Permit::where('status', 'pending')->count();

        // Tabel daily attendance
        $attendances = Attendance::with('student')
            ->whereYear('attendance_date', $selectedYear)
            ->whereMonth('attendance_date', $selectedMonth)
            ->when($search, fn ($q) => $q->whereHas('student', fn ($q) =>
                $q->where('name', 'like', "%$search%")
            ))
            ->latest('attendance_date')
            ->get();

        // Pending permits (sidebar kanan)
        $pendingPermits = Permit::where('status', 'pending')
            ->with('student')->latest()->take(5)->get()
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->student->name,
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

    private function countWorkdays($year, $month): int
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
