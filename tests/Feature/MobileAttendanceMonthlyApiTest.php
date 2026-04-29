<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileAttendanceMonthlyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_attendance_endpoint_returns_calendar_summary(): void
    {
        $user = User::query()->create([
            'name' => 'Vivi Lestari',
            'email' => 'vivi@example.com',
            'password' => 'password',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $user->profile()->create([
            'student_id' => 'MAGANG-100',
            'institution_name' => 'Polindra',
            'major' => 'Teknik Informatika',
            'internship_start' => '2026-04-01',
            'internship_end' => '2026-04-30',
            'status' => 'active',
        ]);

        Attendance::query()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-04-27',
            'check_in_at' => '2026-04-27 08:00:00',
            'status' => 'present',
        ]);

        Permit::query()->create([
            'user_id' => $user->id,
            'type' => 'Sakit',
            'permit_date' => '2026-04-28',
            'reason' => 'Demam',
            'status' => 'pending',
        ]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => 'vivi@example.com',
            'password' => 'password',
        ]);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/attendances/monthly?month=4&year=2026');

        $response->assertOk()
            ->assertJsonPath('summary.present', 1)
            ->assertJsonPath('summary.permit', 1)
            ->assertJsonPath('month.label', 'April 2026');
    }
}
