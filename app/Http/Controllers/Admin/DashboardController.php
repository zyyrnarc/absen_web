<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Student;
use App\Models\Mentor;
use App\Models\Attendance;
use App\Models\Permit;
use App\Models\WeeklyActivity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCampus    = Campus::count();
        $totalMahasiswa = Student::count();
        $totalMentor    = Mentor::count();

        $notifications = Attendance::latest()
            ->take(5)->with('student')->get()
            ->map(fn ($a) => "@{$a->student->name} Hadir Pukul {$a->time} wib")
            ->toArray();

        $selectedMonth = request('bulan', now()->month);
        $months = [
            1=>'Januari 2026',   2=>'Februari 2026',
            3=>'Maret 2026',     4=>'April 2026',
            5=>'Mei 2026',       6=>'Juni 2026',
            7=>'Juli 2026',      8=>'Agustus 2026',
            9=>'September 2026', 10=>'Oktober 2026',
            11=>'November 2026', 12=>'Desember 2026',
        ];

        $days = ['Senin','Selasa','Rabu','Kamis','Jumat'];
        $chartData = [];
        foreach ($days as $i => $day) {
            $date = now()->startOfMonth()->next('Monday')->addDays($i);
            $chartData[$day] = Attendance::whereDate('attendance_date', $date)
                ->where('status', 'hadir')->count();
        }
        $chartMax = max($chartData) ?: 30;

        $weeklyActivities = WeeklyActivity::whereDate('activity_date', today())
            ->with('student')->get()
            ->map(fn ($w) => [
                'day'       => now()->locale('id')->dayName,
                'mahasiswa' => $w->student->name,
                'aktivty'   => $w->activity_name,
            ])->toArray();

        $pendingPermits = Permit::where('status', 'pending')
            ->with('student')->latest()->take(5)->get()
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->student->name,
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
