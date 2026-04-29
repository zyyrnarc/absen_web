<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_intern_can_login_and_view_dashboard_summary(): void
    {
        $user = User::query()->create([
            'name' => 'Vivi Lestari',
            'email' => 'vivi@example.com',
            'username' => 'vivi',
            'password' => 'password',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $user->profile()->create([
            'student_id' => 'MAGANG-100',
            'institution_name' => 'Politeknik Negeri Indramayu',
            'major' => 'Teknik Informatika',
            'study_program' => 'D4 Teknik Informatika',
            'supervisor_name' => 'Pembimbing Lapangan',
            'status' => 'active',
        ]);

        Attendance::query()->create([
            'user_id' => $user->id,
            'attendance_date' => today()->toDateString(),
            'check_in_at' => now()->startOfDay()->addHours(8),
            'status' => 'present',
        ]);

        Permit::query()->create([
            'user_id' => $user->id,
            'type' => 'Sakit',
            'permit_date' => today()->copy()->subDays(2)->toDateString(),
            'reason' => 'Kontrol dokter',
            'status' => 'approved',
        ]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => 'vivi@example.com',
            'password' => 'password',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.profile.student_id', 'MAGANG-100');

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/dashboard');

        $response->assertOk()
            ->assertJsonPath('user.name', 'Vivi Lestari')
            ->assertJsonPath('attendance_today.status', 'present')
            ->assertJsonStructure([
                'monthly_statistics' => ['present', 'permit', 'absent'],
                'quick_actions' => ['can_check_in', 'can_check_out', 'can_submit_permit', 'can_submit_activity'],
                'recent_activities',
            ]);
    }

    public function test_intern_can_submit_permit_from_mobile_api(): void
    {
        $user = User::query()->create([
            'name' => 'Vivi Lestari',
            'email' => 'vivi@example.com',
            'username' => 'vivi',
            'password' => 'password',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $user->profile()->create([
            'student_id' => 'MAGANG-100',
            'institution_name' => 'Politeknik Negeri Indramayu',
            'major' => 'Teknik Informatika',
            'status' => 'active',
        ]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => 'vivi@example.com',
            'password' => 'password',
        ]);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/mobile/permits', [
                'type' => 'Sakit',
                'permit_date' => today()->toDateString(),
                'reason' => 'Demam dan istirahat.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'Sakit')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('permits', [
            'user_id' => $user->id,
            'type' => 'Sakit',
            'status' => 'pending',
        ]);
    }
}
