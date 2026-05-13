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
        $selectedMonth  = max(1, min(12, (int) request('bulan', now()->month)));

        $notifications = Attendance::query()
            ->with(['user', 'student'])
            ->where(function ($query) {
                $query->whereNotNull('check_in_at')
                    ->orWhereNotNull('check_out_at')
                    ->orWhereNotNull('time');
            })
            ->latest('updated_at')
            ->take(20)
            ->get()
            ->flatMap(function (Attendance $attendance) {
                $name = $attendance->user?->name ?? $attendance->student?->name ?? 'Pengguna';
                $events = [];

                if ($attendance->check_out_at) {
                    $events[] = [
                        'time' => $attendance->check_out_at,
                        'message' => "@{$name} Check-out Pukul {$attendance->check_out_at->format('H:i')} WIB",
                    ];
                }

                $checkInTime = $attendance->check_in_at;
                if (! $checkInTime && $attendance->time && $attendance->attendance_date) {
                    $checkInTime = Carbon::parse($attendance->attendance_date->toDateString().' '.$attendance->time);
                }

                if ($checkInTime) {
                    $events[] = [
                        'time' => $checkInTime,
                        'message' => "@{$name} Check-in Pukul {$checkInTime->format('H:i')} WIB",
                    ];
                }

                return $events;
            })
            ->sortByDesc('time')
            ->take(5)
            ->pluck('message')
            ->toArray();

        $months = [
            1=>"Januari {$selectedYear}",   2=>"Februari {$selectedYear}",
            3=>"Maret {$selectedYear}",     4=>"April {$selectedYear}",
            5=>"Mei {$selectedYear}",       6=>"Juni {$selectedYear}",
            7=>"Juli {$selectedYear}",      8=>"Agustus {$selectedYear}",
            9=>"September {$selectedYear}", 10=>"Oktober {$selectedYear}",
            11=>"November {$selectedYear}", 12=>"Desember {$selectedYear}",
        ];

        $monthStart = Carbon::create($selectedYear, $selectedMonth, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $dailyAttendanceCounts = Attendance::query()
            ->selectRaw('DATE(attendance_date) as attendance_day, COUNT(*) as total')
            ->whereYear('attendance_date', $selectedYear)
            ->whereMonth('attendance_date', $selectedMonth)
            ->where(function ($query) {
                $query->whereNotNull('check_in_at')
                    ->orWhereNotNull('time');
            })
            ->groupBy('attendance_day')
            ->pluck('total', 'attendance_day');

        $chartData = [];
        $cursor = $monthStart->copy();

        while ($cursor->lte($monthEnd)) {
            $date = $cursor->toDateString();
            $chartData[$cursor->format('j')] = (int) ($dailyAttendanceCounts[$date] ?? 0);
            $cursor->addDay();
        }

        $chartMax = max(1, max($chartData));

        $weeklyActivities = InternActivity::query()
            ->whereDate('activity_date', today())
            ->with('user')
            ->get()
            ->map(fn ($w) => [
                'day'       => Carbon::parse($w->activity_date)->locale('id')->dayName,
                'mahasiswa' => $w->user?->name ?? 'Pengguna',
                'activity'  => $w->title,
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
