<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            if (Schema::hasColumn('permits', 'student_id')) {
                $table->foreignId('student_id')->nullable()->change();
            }

            if (! Schema::hasColumn('permits', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('student_id')->constrained()->cascadeOnDelete();
            }
        });

        if (
            Schema::hasTable('students')
            && Schema::hasColumn('permits', 'student_id')
            && Schema::hasColumn('permits', 'user_id')
        ) {
            DB::table('permits')
                ->join('students', 'permits.student_id', '=', 'students.id')
                ->join('users', 'users.email', '=', 'students.email')
                ->whereNull('permits.user_id')
                ->select('permits.id as permit_id', 'users.id as user_id')
                ->get()
                ->each(function (object $record): void {
                    DB::table('permits')
                        ->where('id', $record->permit_id)
                        ->update(['user_id' => $record->user_id]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            if (Schema::hasColumn('permits', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('permits', 'student_id')) {
                $table->foreignId('student_id')->nullable(false)->change();
            }
        });
    }
};
