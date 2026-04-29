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
        $sampleCampusName = 'Universitas Contoh';
        $sampleMentorName = 'Pembimbing Lapangan';

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
        $sampleCampusId = DB::table('campuses')->where('name', $sampleCampusName)->value('id');
        $sampleMentorId = DB::table('mentors')->where('name', $sampleMentorName)->value('id');

        if ($internUserId) {
            DB::table('intern_profiles')->updateOrInsert(
                ['user_id' => $internUserId],
                [
                    'student_id' => 'MAGANG-001',
                    'institution_name' => $sampleCampusName,
                    'major' => 'Teknik Informatika',
                    'study_program' => 'D4 RPL',
                    'division' => 'IT Support',
                    'supervisor_name' => $sampleMentorName,
                    'gender' => 'Female',
                    'internship_start' => now()->startOfMonth()->toDateString(),
                    'internship_end' => now()->addMonths(3)->endOfMonth()->toDateString(),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('students')->updateOrInsert(
                ['email' => 'intern@absen-web.test'],
                [
                    'name' => 'Mahasiswa Magang',
                    'nim' => 'MAGANG-001',
                    'major' => 'Teknik Informatika',
                    'study_program' => 'D4 RPL',
                    'campus_id' => $sampleCampusId,
                    'mentor_id' => $sampleMentorId,
                    'campus' => $sampleCampusName,
                    'mentor' => $sampleMentorName,
                    'email' => 'intern@absen-web.test',
                    'username' => null,
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
