<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\InternActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileWeeklyActivityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_activity_endpoint_returns_week_range_and_items(): void
    {
        $user = User::query()->create([
            'name' => 'Vivi Lestari',
            'email' => 'vivi@example.com',
            'password' => 'password',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'attendance_date' => '2026-04-27',
            'check_in_at' => '2026-04-27 08:00:00',
            'check_out_at' => '2026-04-27 16:00:00',
            'status' => 'present',
        ]);

        InternActivity::query()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'activity_date' => '2026-04-27',
            'title' => 'Membuat API',
            'description' => 'Mengerjakan backend mobile',
            'status' => 'submitted',
        ]);

        $login = $this->postJson('/api/mobile/login', [
            'email' => 'vivi@example.com',
            'password' => 'password',
        ]);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/activities/weekly?date=2026-04-29');

        $response->assertOk()
            ->assertJsonPath('summary.count', 1)
            ->assertJsonPath('items.0.title', 'Membuat API');
    }
}
