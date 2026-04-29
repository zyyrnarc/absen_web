<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Mentor;
use App\Models\Attendance;
use App\Models\Permit;
use App\Models\InternActivity;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCampus    = Campus::count();
        $totalMahasiswa = User::query()->where('role', 'intern')->count();
        $totalMentor    = Mentor::count();
        $selectedYear   = (int) request('year', now()->year);

        $notifications = Attendance::query()
            ->with(['user', 'student'])
            ->where(function ($query) {
                $query->whereNotNull('check_in_at')
                    ->orWhereNotNull('time');
            })
            ->latest('check_in_at')
            ->take(5)
            ->get()
            ->map(function (Attendance $attendance): string {
                $name = $attendance->user?->name ?? $attendance->student?->name ?? 'Pengguna';
                $time = $attendance->check_in_at?->format('H:i') ?? $attendance->time ?? '-';

                return "@{$name} Hadir Pukul {$time} WIB";
            })
            ->toArray();

        $selectedMonth = request('bulan', now()->month);
        $months = [
            1=>"Januari {$selectedYear}",   2=>"Februari {$selectedYear}",
            3=>"Maret {$selectedYear}",     4=>"April {$selectedYear}",
            5=>"Mei {$selectedYear}",       6=>"Juni {$selectedYear}",
            7=>"Juli {$selectedYear}",      8=>"Agustus {$selectedYear}",
            9=>"September {$selectedYear}", 10=>"Oktober {$selectedYear}",
            11=>"November {$selectedYear}", 12=>"Desember {$selectedYear}",
        ];

        $days = ['Senin','Selasa','Rabu','Kamis','Jumat'];
        $chartData = array_fill_keys($days, 0);

        $monthlyAttendances = Attendance::query()
            ->whereYear('attendance_date', $selectedYear)
            ->whereMonth('attendance_date', $selectedMonth)
            ->where(function ($query) {
                $query->whereNotNull('check_in_at')
                    ->orWhereNotNull('time');
            })
            ->get();

        foreach ($monthlyAttendances as $attendance) {
            $dayName = Carbon::parse($attendance->attendance_date)->locale('id')->dayName;

            if (array_key_exists($dayName, $chartData)) {
                $chartData[$dayName]++;
            }
        }
        $chartMax = max($chartData) ?: 30;

        $weeklyActivities = InternActivity::query()
            ->whereDate('activity_date', today())
            ->with('user')
            ->get()
            ->map(fn ($w) => [
                'day'       => Carbon::parse($w->activity_date)->locale('id')->dayName,
                'mahasiswa' => $w->user?->name ?? 'Pengguna',
                'aktivty'   => $w->title,
            ])->toArray();

        $pendingPermits = Permit::where('status', 'pending')
            ->with(['user', 'student'])->latest('permit_date')->take(5)->get()
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->user?->name ?? $p->student?->name ?? 'Pengguna',
                'type' => $p->type,
                'date' => Carbon::parse($p->permit_date)
                            ->locale('id')->isoFormat('D MMMM YYYY'),
            ])->toArray();

        return view('admin.dashboard.index', compact(
            'totalCampus','totalMahasiswa','totalMentor',
            'notifications','months','selectedMonth',
            'chartData','chartMax','weeklyActivities','pendingPermits'
        ));
    }

    public function approvePermit(int $id)
    {
        Permit::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', "Permit #{$id} berhasil di-approve.");
    }
}
