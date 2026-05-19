<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttendanceActionRequest;
use App\Models\AppSetting;
use App\Models\Attendance;
use App\Models\Mentor;
use App\Models\Permit;
use App\Models\Student;
use App\Support\MobileApiAuth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $todayPermit = Permit::query()
            ->where('user_id', $user->id)
            ->whereDate('permit_date', today())
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
                'can_check_in' => ! $todayAttendance?->check_in_at && ! $todayPermit,
                'can_check_out' => (bool) $todayAttendance?->check_in_at && ! $todayAttendance?->check_out_at,
                'can_submit_permit' => true,
                'export_endpoint' => url('/api/mobile/attendances/monthly/export?month='.$month.'&year='.$year),
            ],
        ]);
    }

    public function monthlyExport(Request $request)
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
        $filename = 'daftar-hadir-'.$user->id.'-'.$monthData['month_start']->format('Y-m').'.pdf';
        $pdf = $this->renderMonthlyAttendancePdf($user, $monthData);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
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

        $todayPermit = Permit::query()
            ->where('user_id', $user->id)
            ->whereDate('permit_date', today())
            ->first();

        if ($todayPermit) {
            return response()->json([
                'message' => 'Hari ini sudah mengajukan permit, check-in tidak bisa dilakukan.',
            ], 422);
        }

        $attendance = Attendance::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'attendance_date' => today()->toDateString(),
            ],
            [
                'student_id' => $this->legacyStudentId($user),
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

            $isWorkday = ! $isWeekend && $isWithinInternship;

            $permitStatus = $permit ? 'permit' : null;

            if ($isWorkday && $attendance?->check_in_at) {
                $status = 'present';
                $summary['present']++;
            } elseif ($isWorkday && $permit) {
                $status = 'permit';
                $summary['permit']++;
            } elseif ($this->shouldMarkAbsent($cursor, $isWeekend, $isFuture, $isWithinInternship, $attendance)) {
                $status = 'absent';
                $summary['absent']++;
            }

            if ($this->shouldCountAsElapsedWorkday($cursor, $isWeekend, $isFuture, $isWithinInternship, $attendance, $permit)) {
                $summary['workdays']++;
            }

            $days[] = [
                'date' => $date,
                'day' => (int) $cursor->day,
                'weekday' => $cursor->copy()->locale('en')->translatedFormat('D'),
                'status' => $status,
                'permit_marker' => $permitStatus,
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
            ->get();

        $calendar = $this->buildMonthlyCalendar(
            $user,
            $monthStart,
            $monthEnd,
            $attendances,
            $permits->keyBy(fn (Permit $permit) => $permit->permit_date->toDateString())
        );

        $calendar['summary'] = $this->buildMonthlySummary(
            $user,
            $monthStart,
            $attendances,
            $permits,
            (int) $calendar['summary']['workdays']
        );

        return [
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
            'calendar' => $calendar,
        ];
    }

    private function buildMonthlySummary($user, Carbon $monthStart, Collection $attendances, Collection $permits, int $workdays): array
    {
        $storedAbsentCount = $attendances
            ->filter(fn (Attendance $attendance) => $attendance->status === 'absent')
            ->count();
        $isCurrentMonth = $monthStart->isSameMonth(today()) && $monthStart->isSameYear(today());
        $todayAttendance = $isCurrentMonth ? $attendances->get(today()->toDateString()) : null;
        $todayPermit = $isCurrentMonth
            ? $permits->first(fn (Permit $permit) => $permit->permit_date->isToday())
            : null;
        $shouldCountTodayAbsent = $isCurrentMonth &&
            ! $todayPermit &&
            $this->shouldMarkAbsent(
                today(),
                today()->isWeekend(),
                false,
                $this->isWithinInternshipPeriod($user, today()),
                $todayAttendance
            );
        $todayAbsentAlreadyStored = $todayAttendance?->status === 'absent';

        return [
            'present' => $attendances
                ->filter(fn (Attendance $attendance) => (bool) $attendance->check_in_at)
                ->count(),
            'permit' => $permits->count(),
            'absent' => $storedAbsentCount + ($shouldCountTodayAbsent && ! $todayAbsentAlreadyStored ? 1 : 0),
            'workdays' => $workdays,
        ];
    }

    private function legacyStudentId($user): ?int
    {
        $user->loadMissing('profile');

        $student = Student::query()
            ->where('email', $user->email)
            ->when(
                $user->profile?->student_id,
                fn ($query, $studentId) => $query->orWhere('nim', $studentId)
            )
            ->first();

        if ($student) {
            return $student->id;
        }

        return Student::query()->create([
            'name' => $user->name,
            'nim' => $user->profile?->student_id,
            'major' => $user->profile?->major,
            'study_program' => $user->profile?->study_program,
            'email' => $user->email,
            'status' => $user->is_active ? 'active' : 'inactive',
        ])->id;
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

    private function shouldMarkAbsent(Carbon $date, bool $isWeekend, bool $isFuture, bool $isWithinInternship, ?Attendance $attendance): bool
    {
        if ($isWeekend || $isFuture || ! $isWithinInternship) {
            return false;
        }

        if ($attendance?->status === 'absent') {
            return true;
        }

        if (! $date->isToday()) {
            return false;
        }

        return now()->greaterThanOrEqualTo(today()->setTime(16, 0));
    }

    private function shouldCountAsElapsedWorkday(Carbon $date, bool $isWeekend, bool $isFuture, bool $isWithinInternship, ?Attendance $attendance, ?Permit $permit): bool
    {
        if ($isWeekend || $isFuture || ! $isWithinInternship) {
            return false;
        }

        if (! $date->isToday()) {
            return true;
        }

        return $attendance?->check_in_at || $permit || now()->greaterThanOrEqualTo(today()->setTime(16, 0));
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

    private function renderMonthlyAttendancePdf($user, array $monthData): string
    {
        $profile = $user->profile;
        $setting = AppSetting::query()->first();
        $monthStart = $monthData['month_start'];
        $monthEnd = $monthData['month_end'];
        $rows = collect($monthData['calendar']['days'])
            ->reject(fn (array $day) => (bool) ($day['is_weekend'] ?? false))
            ->values();
        $rowCount = max(22, $rows->count());
        $rowHeight = $rowCount > 23 ? 12 : 13;
        $tableTop = 672;
        $headerHeight = 28;
        $tableLeft = 60;
        $tableWidth = 475;
        $tableBottom = $tableTop - $headerHeight - ($rowCount * $rowHeight);
        $columnWidths = [28, 140, 22, 22, 22, 22, 22, 82, 115];
        $columns = [$tableLeft];

        foreach ($columnWidths as $width) {
            $columns[] = end($columns) + $width;
        }

        $commands = ['0 G', '0.7 w'];
        $text = function (float $x, float $y, int $size, string $value, bool $bold = false) use (&$commands): void {
            $font = $bold ? 'F2' : 'F1';
            $commands[] = 'BT /'.$font.' '.$size.' Tf '.$this->pdfNumber($x).' '.$this->pdfNumber($y).' Td ('.$this->pdfEscape($value).') Tj ET';
        };
        $estimateWidth = fn (int $size, string $value, bool $bold = false): float => $this->pdfTextWidth($value, $size, $bold);
        $center = function (float $centerX, float $y, int $size, string $value, bool $bold = false) use ($text, $estimateWidth): void {
            $estimatedWidth = $estimateWidth($size, $value, $bold);
            $text($centerX - ($estimatedWidth / 2), $y, $size, $value, $bold);
        };
        $line = function (float $x1, float $y1, float $x2, float $y2) use (&$commands): void {
            $commands[] = $this->pdfNumber($x1).' '.$this->pdfNumber($y1).' m '.$this->pdfNumber($x2).' '.$this->pdfNumber($y2).' l S';
        };
        $check = function (float $centerX, float $centerY) use ($line): void {
            $line($centerX - 4, $centerY, $centerX - 1, $centerY - 3);
            $line($centerX - 1, $centerY - 3, $centerX + 5, $centerY + 5);
        };

        $academicYearStart = $monthStart->month >= 7 ? $monthStart->year : $monthStart->year - 1;
        $academicYear = $academicYearStart.'/'.($academicYearStart + 1);
        $institution = $profile?->institution_name ?: 'POLITEKNIK NEGERI INDRAMAYU';
        $nimName = trim(($profile?->student_id ?: '-').' / '.$user->name);
        $study = trim(($profile?->major ?: '-').' / '.($profile?->study_program ?: '-'));
        $industry = $setting?->company_name ?: ($profile?->division ?: '-');
        [$supervisor, $supervisorPosition] = $this->signatureSupervisor($user->mentor_name ?: $profile?->supervisor_name, $user->mentor_position);

        $center(297.5, 782, 9, Str::upper($institution), true);
        $center(297.5, 770, 9, 'DAFTAR HADIR MAHASISWA - PROGRAM MAGANG INDUSTRI', true);
        $center(297.5, 758, 9, 'TAHUN AKADEMIK '.$academicYear, true);

        $text(60, 733, 8, 'NIM / Nama', true);
        $text(188, 733, 8, ':', true);
        $text(198, 733, 8, $nimName, true);
        $text(60, 718, 8, 'Jurusan / Program Studi', true);
        $text(188, 718, 8, ':', true);
        $text(198, 718, 8, $study, true);
        $text(60, 703, 8, 'Industri', true);
        $text(188, 703, 8, ':', true);
        $text(198, 703, 8, $industry, true);

        foreach ([$columns[0], $columns[1], $columns[2], $columns[7], $columns[8], $columns[9]] as $x) {
            $line($x, $tableTop, $x, $tableBottom);
        }

        foreach ([$columns[3], $columns[4], $columns[5], $columns[6]] as $x) {
            $line($x, $tableTop - 14, $x, $tableBottom);
        }

        $line($tableLeft, $tableTop, $tableLeft + $tableWidth, $tableTop);
        $line($tableLeft, $tableTop - $headerHeight, $tableLeft + $tableWidth, $tableTop - $headerHeight);
        $line($tableLeft, $tableBottom, $tableLeft + $tableWidth, $tableBottom);

        for ($i = 1; $i < $rowCount; $i++) {
            $y = $tableTop - $headerHeight - ($i * $rowHeight);
            $line($tableLeft, $y, $tableLeft + $tableWidth, $y);
        }

        $line($columns[2], $tableTop - 14, $columns[7], $tableTop - 14);
        $parafMiddle = ($columns[8] + $columns[9]) / 2;
        $line($parafMiddle, $tableTop - $headerHeight, $parafMiddle, $tableBottom);

        $center(($columns[0] + $columns[1]) / 2, $tableTop - 18, 8, 'No', true);
        $center(($columns[1] + $columns[2]) / 2, $tableTop - 18, 8, 'Hari / Tanggal', true);
        $center(($columns[2] + $columns[7]) / 2, $tableTop - 10, 8, 'Kehadiran', true);
        foreach (['H', 'I', 'S', 'B', 'T'] as $index => $label) {
            $center(($columns[2 + $index] + $columns[3 + $index]) / 2, $tableTop - 24, 7, $label, true);
        }
        $center(($columns[7] + $columns[8]) / 2, $tableTop - 18, 8, 'Ket.', true);
        $center(($columns[8] + $columns[9]) / 2, $tableTop - 12, 7, 'Paraf', true);
        $center(($columns[8] + $columns[9]) / 2, $tableTop - 23, 7, 'Pembimbing Lapangan', true);

        for ($i = 0; $i < $rowCount; $i++) {
            $day = $rows->get($i);
            $y = $tableTop - $headerHeight - ($i * $rowHeight) - 9;
            $center(($columns[0] + $columns[1]) / 2, $y, 8, (string) ($i + 1));

            if (! $day) {
                $this->writeParafNumber($text, $columns[8], $columns[9], $y, $i + 1);
                continue;
            }

            $date = Carbon::parse($day['date']);
            $text($columns[1] + 5, $y, 6, $this->indonesianDayName($date).',');
            $text($columns[1] + 38, $y, 6, $this->indonesianDateWithoutDay($date));
            $mark = $this->attendanceReportMark($day);
            $markIndex = array_search($mark, ['H', 'I', 'S', 'B', 'T'], true);

            if ($markIndex !== false) {
                $check(($columns[2 + $markIndex] + $columns[3 + $markIndex]) / 2, $y + 2);
            }

            $this->writeParafNumber($text, $columns[8], $columns[9], $y, $i + 1);
        }

        $legendY = $tableBottom - 18;
        $text(60, $legendY, 8, 'H = Hadir     I = Ijin        S = Sakit        B = Bolos/Alfa        T = Terlambat', true);
        $text(60, $legendY - 15, 8, 'Catatan :', true);
        $text(70, $legendY - 29, 7, '- Bagi Mahasiswa yang Ijin atau Sakit, harap mengkonfirmasi ke Dosen Pembimbing Magang ataupun');
        $text(78, $legendY - 41, 7, 'Pembimbing Industri Magang.');
        $text(70, $legendY - 55, 7, '- Mahasiswa diwajibkan mengirim salinan daftar hadir harian kepada dosen pembimbing');
        $text(78, $legendY - 67, 7, '(via email/WA dan upload di drive) di setiap bulan atau mingguan.');

        $signatureDate = $rows->filter()->last()['date'] ?? $monthEnd->toDateString();
        $signature = Carbon::parse($signatureDate);
        $text(360, 214, 8, 'Indramayu, '.$this->indonesianDateWithoutDay($signature));
        $text(360, 201, 8, 'Pembimbing Industri,');
        $text(360, 148, 8, $supervisor, true);
        $line(360, 143, 360 + $estimateWidth(8, $supervisor, true), 143);
        $text(360, 135, 8, $supervisorPosition);

        return $this->buildSimplePdf($commands);
    }

    private function attendanceReportMark(array $day): ?string
    {
        $status = $day['status'] ?? null;
        $normalizedStatus = Str::lower((string) $status);
        $permitType = Str::lower((string) ($day['permit_type'] ?? ''));

        if (($day['permit_marker'] ?? null) === 'permit' || $status === 'permit') {
            return Str::contains($permitType, 'sakit') ? 'S' : 'I';
        }

        if ($normalizedStatus === 'absent') {
            return 'B';
        }

        if (in_array($normalizedStatus, ['late', 'terlambat'], true)) {
            return 'T';
        }

        if ($normalizedStatus === 'present' || ($day['check_in_time'] ?? null)) {
            return 'H';
        }

        return null;
    }

    private function signatureSupervisor(?string $supervisorName, ?string $fallbackPosition = null): array
    {
        $name = trim((string) $supervisorName);
        $mentor = $name !== ''
            ? Mentor::query()->where('name', $name)->first()
            : null;

        if (! $mentor && $name !== '') {
            $mentor = Mentor::query()
                ->where('name', 'like', '%'.$name.'%')
                ->first();
        }

        $baseName = trim(Str::before($name, ','));
        if (! $mentor && $baseName !== '' && $baseName !== $name) {
            $mentor = Mentor::query()
                ->where('name', 'like', '%'.$baseName.'%')
                ->first();
        }

        $position = trim((string) ($mentor?->position ?: $fallbackPosition));
        if (Str::lower($position) === 'pembimbing industri') {
            $position = '';
        }

        return [
            $mentor?->name ?: ($name !== '' ? $name : 'Pembimbing Industri'),
            $position !== '' ? $position : '-',
        ];
    }

    private function writeParafNumber(callable $text, float $left, float $right, float $y, int $number): void
    {
        $middle = ($left + $right) / 2;
        $x = $number % 2 === 1
            ? $left + 4
            : $middle + 4;

        $text($x, $y, 7, (string) $number);
    }

    private function indonesianDate(Carbon $date): string
    {
        return $this->indonesianDayName($date).', '.$this->indonesianDateWithoutDay($date);
    }

    private function indonesianDayName(Carbon $date): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];

        return $days[$date->dayOfWeek];
    }

    private function indonesianDateWithoutDay(Carbon $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->day.' '.$months[$date->month].' '.$date->year;
    }

    private function buildSimplePdf(array $commands): string
    {
        $stream = implode("\n", $commands)."\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            "<< /Length ".strlen($stream)." >>\nstream\n".$stream."endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $number = $index + 1;
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf('%010d 00000 n ', $offset)."\n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->pdfPlainText($value));
    }

    private function pdfPlainText(string $value): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/[^\x20-\x7E]/', '', $text ?: $value) ?? '';
    }

    private function pdfTextWidth(string $value, float $size, bool $bold = false): float
    {
        $widths = [
            ' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556, '%' => 889, '&' => 667, "'" => 191,
            '(' => 333, ')' => 333, '*' => 389, '+' => 584, ',' => 278, '-' => 333, '.' => 278, '/' => 278,
            '0' => 556, '1' => 556, '2' => 556, '3' => 556, '4' => 556, '5' => 556, '6' => 556, '7' => 556,
            '8' => 556, '9' => 556, ':' => 333, ';' => 333, '<' => 584, '=' => 584, '>' => 584, '?' => 611,
            '@' => 975, 'A' => 667, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778,
            'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 611, 'M' => 833, 'N' => 722, 'O' => 778,
            'P' => 667, 'Q' => 778, 'R' => 722, 'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667, 'W' => 944,
            'X' => 667, 'Y' => 667, 'Z' => 611, '[' => 333, '\\' => 278, ']' => 333, '^' => 584, '_' => 556,
            '`' => 333, 'a' => 556, 'b' => 611, 'c' => 556, 'd' => 611, 'e' => 556, 'f' => 333, 'g' => 611,
            'h' => 611, 'i' => 278, 'j' => 278, 'k' => 556, 'l' => 278, 'm' => 889, 'n' => 611, 'o' => 611,
            'p' => 611, 'q' => 611, 'r' => 389, 's' => 556, 't' => 333, 'u' => 611, 'v' => 556, 'w' => 778,
            'x' => 556, 'y' => 556, 'z' => 500, '{' => 389, '|' => 280, '}' => 389, '~' => 584,
        ];

        $units = 0;
        $plainText = $this->pdfPlainText($value);

        for ($i = 0; $i < strlen($plainText); $i++) {
            $units += $widths[$plainText[$i]] ?? 556;
        }

        return ($units / 1000) * $size;
    }

    private function pdfNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
