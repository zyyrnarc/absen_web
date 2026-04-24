<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'campus')) {
                $table->string('campus')->nullable()->after('study_program');
            }

            if (! Schema::hasColumn('students', 'mentor')) {
                $table->string('mentor')->nullable()->after('campus');
            }

            if (! Schema::hasColumn('students', 'password')) {
                $table->string('password')->nullable()->after('username');
            }
        });

        if (Schema::hasColumn('students', 'campus_id')) {
            DB::table('students')
                ->leftJoin('campuses', 'students.campus_id', '=', 'campuses.id')
                ->whereNull('students.campus')
                ->whereNotNull('campuses.name')
                ->update([
                    'students.campus' => DB::raw('campuses.name'),
                ]);
        }

        if (Schema::hasColumn('students', 'mentor_id')) {
            DB::table('students')
                ->leftJoin('mentors', 'students.mentor_id', '=', 'mentors.id')
                ->whereNull('students.mentor')
                ->whereNotNull('mentors.name')
                ->update([
                    'students.mentor' => DB::raw('mentors.name'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $drops = [];

            foreach (['campus', 'mentor', 'password'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
