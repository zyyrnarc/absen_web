<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $adminUserId = DB::table('users')->where('email', 'admin@absen-web.test')->value('id');
        $internUserId = DB::table('users')->where('email', 'intern@absen-web.test')->value('id');
        $legacyStudentId = DB::table('students')->where('email', 'intern@absen-web.test')->value('id');

        if (! $adminUserId || ! $internUserId || ! $legacyStudentId) {
            return;
        }

        $pendingPermitDate = $this->previousWeekday(today()->copy()->subDay());
        $approvedPermitDate = $this->previousWeekday($pendingPermitDate->copy()->subDays(4));

        $attendanceDates = [];
        $cursor = today()->copy();

        while (count($attendanceDates) < 6) {
            if (
                $cursor->isWeekday()
                && ! $cursor->isSameDay($pendingPermitDate)
                && ! $cursor->isSameDay($approvedPermitDate)
            ) {
                $attendanceDates[] = $cursor->copy();
            }

            $cursor->subDay();
        }

        $attendanceDates = array_reverse($attendanceDates);

        foreach ($attendanceDates as $index => $date) {
            $checkIn = $date->copy()->setTime(8, ($index % 3) * 5);
            $checkOut = $date->copy()->setTime(16, ($index % 2) * 10);

            DB::table('attendances')->updateOrInsert(
                [
                    'user_id' => $internUserId,
                    'attendance_date' => $date->toDateString(),
                ],
                [
                    'student_id' => $legacyStudentId,
                    'time' => $checkIn->format('H:i:s'),
                    'check_in_at' => $checkIn->toDateTimeString(),
                    'check_out_at' => $checkOut->toDateTimeString(),
                    'check_in_latitude' => -6.3265000,
                    'check_in_longitude' => 108.3245000,
                    'check_out_latitude' => -6.3265000,
                    'check_out_longitude' => 108.3245000,
                    'notes' => 'Sample attendance seeded for demo dashboard.',
                    'status' => 'present',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $activityTemplates = [
            [
                'title' => 'Membuat API absensi',
                'description' => 'Mengerjakan endpoint login, dashboard, dan check-in mobile.',
                'status' => 'approved',
            ],
            [
                'title' => 'Menguji integrasi mobile',
                'description' => 'Melakukan pengujian request profile, permit, dan weekly activity.',
                'status' => 'submitted',
            ],
            [
                'title' => 'Merapikan dokumentasi API',
                'description' => 'Memperbarui dokumentasi endpoint dan contoh request response.',
                'status' => 'approved',
            ],
        ];

        $activityDates = array_slice(array_reverse($attendanceDates), 0, 3);
        $activityDates = array_reverse($activityDates);

        foreach ($activityDates as $index => $date) {
            $attendanceId = DB::table('attendances')
                ->where('user_id', $internUserId)
                ->whereDate('attendance_date', $date->toDateString())
                ->value('id');

            $template = $activityTemplates[$index] ?? $activityTemplates[0];

            DB::table('intern_activities')->updateOrInsert(
                [
                    'user_id' => $internUserId,
                    'activity_date' => $date->toDateString(),
                    'title' => $template['title'],
                ],
                [
                    'attendance_id' => $attendanceId,
                    'description' => $template['description'],
                    'status' => $template['status'],
                    'reviewed_by' => $template['status'] === 'approved' ? $adminUserId : null,
                    'reviewed_at' => $template['status'] === 'approved' ? $now : null,
                    'admin_notes' => $template['status'] === 'approved' ? 'Aktivitas sample disetujui untuk demo.' : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $permits = [
            [
                'type' => 'Izin',
                'permit_date' => $pendingPermitDate,
                'reason' => 'Keperluan keluarga untuk sample tampilan permit pending.',
                'status' => 'pending',
            ],
            [
                'type' => 'Sakit',
                'permit_date' => $approvedPermitDate,
                'reason' => 'Istirahat karena kondisi kesehatan kurang baik.',
                'status' => 'approved',
            ],
        ];

        foreach ($permits as $permit) {
            DB::table('permits')->updateOrInsert(
                [
                    'user_id' => $internUserId,
                    'permit_date' => $permit['permit_date']->toDateString(),
                    'type' => $permit['type'],
                ],
                [
                    'student_id' => $legacyStudentId,
                    'reason' => $permit['reason'],
                    'attachment_path' => null,
                    'attachment_original_name' => null,
                    'status' => $permit['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function previousWeekday(Carbon $date): Carbon
    {
        while (! $date->isWeekday()) {
            $date->subDay();
        }

        return $date;
    }
}
