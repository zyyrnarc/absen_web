<?php
namespace App\Http\Controllers;

use App\Models\WeeklyActivity;
use App\Models\Student;
use Carbon\Carbon;

class WeeklyActivityController extends Controller
{
    public function index()
    {
        $selectedMonth  = request('month', now()->month);
        $selectedYear   = request('year', now()->year);
        $selectedStudent = request('student_id');

        $students = Student::orderBy('name')->get();

        $activities = WeeklyActivity::with('student')
            ->whereYear('activity_date', $selectedYear)
            ->whereMonth('activity_date', $selectedMonth)
            ->when($selectedStudent, fn ($q) => $q->where('student_id', $selectedStudent))
            ->orderBy('activity_date')
            ->get()
            ->map(fn ($a) => [
                'id'       => $a->id,
                'name'     => $a->student->name,
                'day'      => Carbon::parse($a->activity_date)->locale('en')->dayName,
                'times'    => '09.00 - 16.00',
                'activity' => $a->activity_name,
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
}
