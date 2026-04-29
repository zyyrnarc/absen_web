<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('app_settings')->updateOrInsert(
            ['id' => 1],
            [
                'company_name' => 'Absen Web',
                'company_address' => 'Jl. Pendidikan No. 1, Indramayu',
                'company_logo_path' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $campuses = [
            [
                'name' => 'Politeknik Negeri Indramayu',
                'address' => 'Indramayu, Jawa Barat',
            ],
            [
                'name' => 'Universitas Contoh',
                'address' => 'Cirebon, Jawa Barat',
            ],
        ];

        foreach ($campuses as $campus) {
            DB::table('campuses')->updateOrInsert(
                ['name' => $campus['name']],
                [
                    'address' => $campus['address'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $majors = [
            ['name' => 'Teknik Informatika', 'study_program' => 'D4 RPL'],
            ['name' => 'Sistem Informasi', 'study_program' => 'S1 Sistem Informasi'],
            ['name' => 'Manajemen Informatika', 'study_program' => 'D3 Manajemen Informatika'],
        ];

        foreach ($majors as $major) {
            DB::table('majors')->updateOrInsert(
                ['name' => $major['name']],
                [
                    'study_program' => $major['study_program'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $campusIds = DB::table('campuses')
            ->whereIn('name', ['Politeknik Negeri Indramayu', 'Universitas Contoh'])
            ->pluck('id', 'name');

        $mentors = [
            [
                'name' => 'Budi Santoso',
                'position' => 'IT Supervisor',
                'email' => 'budi.santoso@absen-web.test',
                'campus_id' => $campusIds['Politeknik Negeri Indramayu'] ?? null,
            ],
            [
                'name' => 'Siti Rahma',
                'position' => 'Koordinator Magang',
                'email' => 'siti.rahma@absen-web.test',
                'campus_id' => $campusIds['Universitas Contoh'] ?? null,
            ],
        ];

        foreach ($mentors as $mentor) {
            DB::table('mentors')->updateOrInsert(
                ['email' => $mentor['email']],
                [
                    'name' => $mentor['name'],
                    'position' => $mentor['position'],
                    'campus_id' => $mentor['campus_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
