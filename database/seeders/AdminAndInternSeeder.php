<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAndInternSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@absen-web.test'],
            [
                'name' => 'Admin Absen',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081200000001',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'intern@absen-web.test'],
            [
                'name' => 'Mahasiswa Magang',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'phone' => '081200000002',
                'is_active' => true,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $internUserId = DB::table('users')->where('email', 'intern@absen-web.test')->value('id');

        if ($internUserId) {
            DB::table('intern_profiles')->updateOrInsert(
                ['user_id' => $internUserId],
                [
                    'student_id' => 'MAGANG-001',
                    'institution_name' => 'Universitas Contoh',
                    'major' => 'Teknik Informatika',
                    'study_program' => 'D4 RPL',
                    'division' => 'IT Support',
                    'supervisor_name' => 'Pembimbing Lapangan',
                    'gender' => 'Female',
                    'internship_start' => now()->startOfMonth()->toDateString(),
                    'internship_end' => now()->addMonths(3)->endOfMonth()->toDateString(),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
