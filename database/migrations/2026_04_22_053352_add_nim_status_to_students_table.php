<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'nim')) {
                $table->string('nim')->unique()->nullable();
            }

            if (! Schema::hasColumn('students', 'study_program')) {
                $table->string('study_program')->nullable();
            }

            if (! Schema::hasColumn('students', 'username')) {
                $table->string('username')->unique()->nullable();
            }

            if (! Schema::hasColumn('students', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('students', 'username')) {
                $table->dropColumn('username');
            }

            if (Schema::hasColumn('students', 'study_program')) {
                $table->dropColumn('study_program');
            }

            if (Schema::hasColumn('students', 'nim')) {
                $table->dropColumn('nim');
            }
        });
    }
};
