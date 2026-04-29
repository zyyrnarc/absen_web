<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->date('attendance_date');
                $table->time('time')->nullable();
                $table->timestamp('check_in_at')->nullable();
                $table->timestamp('check_out_at')->nullable();
                $table->decimal('check_in_latitude', 10, 7)->nullable();
                $table->decimal('check_in_longitude', 10, 7)->nullable();
                $table->decimal('check_out_latitude', 10, 7)->nullable();
                $table->decimal('check_out_longitude', 10, 7)->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('present');
                $table->timestamps();

                $table->unique(['user_id', 'attendance_date']);
            });

            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('student_id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('attendances', 'check_in_at')) {
                $table->timestamp('check_in_at')->nullable()->after('time');
            }

            if (! Schema::hasColumn('attendances', 'check_out_at')) {
                $table->timestamp('check_out_at')->nullable()->after('check_in_at');
            }

            if (! Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_out_at');
            }

            if (! Schema::hasColumn('attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            }

            if (! Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_in_longitude');
            }

            if (! Schema::hasColumn('attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            }

            if (! Schema::hasColumn('attendances', 'notes')) {
                $table->text('notes')->nullable()->after('check_out_longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
