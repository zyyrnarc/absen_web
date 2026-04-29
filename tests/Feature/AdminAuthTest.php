<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_intern_cannot_login_to_admin_panel(): void
    {
        User::query()->create([
            'name' => 'Intern User',
            'email' => 'intern@example.com',
            'password' => 'password',
            'role' => 'intern',
            'is_active' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'intern@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }
}
