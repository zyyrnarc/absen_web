<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique('users_username_unique');
                $table->dropColumn('username');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'username')) {
                $table->dropUnique('students_username_unique');
                $table->dropColumn('username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }
        });
    }
};
