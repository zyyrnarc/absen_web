<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            if (! Schema::hasColumn('campuses', 'name')) {
                $table->string('name')->nullable();
            }

            if (! Schema::hasColumn('campuses', 'address')) {
                $table->string('address')->nullable();
            }
        });

        Schema::table('mentors', function (Blueprint $table) {
            if (! Schema::hasColumn('mentors', 'name')) {
                $table->string('name')->nullable();
            }

            if (! Schema::hasColumn('mentors', 'email')) {
                $table->string('email')->unique()->nullable();
            }

            if (! Schema::hasColumn('mentors', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'name')) {
                $table->string('name')->nullable();
            }

            if (! Schema::hasColumn('students', 'major')) {
                $table->string('major')->nullable();
            }

            if (! Schema::hasColumn('students', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'mentor_id')) {
                $table->foreignId('mentor_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'email')) {
                $table->string('email')->unique()->nullable();
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('attendances', 'attendance_date')) {
                $table->date('attendance_date')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'time')) {
                $table->time('time')->nullable();
            }

            if (! Schema::hasColumn('attendances', 'status')) {
                $table->string('status')->nullable();
            }
        });

        Schema::table('permits', function (Blueprint $table) {
            if (! Schema::hasColumn('permits', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('permits', 'type')) {
                $table->string('type')->nullable();
            }

            if (! Schema::hasColumn('permits', 'permit_date')) {
                $table->date('permit_date')->nullable();
            }

            if (! Schema::hasColumn('permits', 'reason')) {
                $table->text('reason')->nullable();
            }

            if (! Schema::hasColumn('permits', 'status')) {
                $table->string('status')->default('pending');
            }
        });

        Schema::table('weekly_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('weekly_activities', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('weekly_activities', 'activity_name')) {
                $table->string('activity_name')->nullable();
            }

            if (! Schema::hasColumn('weekly_activities', 'activity_date')) {
                $table->date('activity_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('weekly_activities', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn('weekly_activities', 'activity_date')) {
                $drops[] = 'activity_date';
            }

            if (Schema::hasColumn('weekly_activities', 'activity_name')) {
                $drops[] = 'activity_name';
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }

            if (Schema::hasColumn('weekly_activities', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }
        });

        Schema::table('permits', function (Blueprint $table) {
            $drops = [];

            foreach (['type', 'permit_date', 'reason', 'status'] as $column) {
                if (Schema::hasColumn('permits', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }

            if (Schema::hasColumn('permits', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            $drops = [];

            foreach (['attendance_date', 'time', 'status'] as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }

            if (Schema::hasColumn('attendances', 'student_id')) {
                $table->dropConstrainedForeignId('student_id');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $drops = [];

            foreach (['name', 'major', 'email'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }

            if (Schema::hasColumn('students', 'mentor_id')) {
                $table->dropConstrainedForeignId('mentor_id');
            }

            if (Schema::hasColumn('students', 'campus_id')) {
                $table->dropConstrainedForeignId('campus_id');
            }
        });

        Schema::table('mentors', function (Blueprint $table) {
            $drops = [];

            foreach (['name', 'email'] as $column) {
                if (Schema::hasColumn('mentors', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }

            if (Schema::hasColumn('mentors', 'campus_id')) {
                $table->dropConstrainedForeignId('campus_id');
            }
        });

        Schema::table('campuses', function (Blueprint $table) {
            $drops = [];

            foreach (['name', 'address'] as $column) {
                if (Schema::hasColumn('campuses', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
