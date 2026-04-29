<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }
        });

        Schema::table('intern_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('intern_profiles', 'study_program')) {
                $table->string('study_program')->nullable()->after('major');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('intern_profiles', 'study_program')) {
                $table->dropColumn('study_program');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique('users_username_unique');
                $table->dropColumn('username');
            }
        });
    }
};
