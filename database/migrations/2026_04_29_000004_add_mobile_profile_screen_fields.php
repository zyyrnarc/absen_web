<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('phone');
            }
        });

        Schema::table('intern_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('intern_profiles', 'gender')) {
                $table->string('gender')->nullable()->after('supervisor_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('intern_profiles', 'gender')) {
                $table->dropColumn('gender');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'avatar_path')) {
                $table->dropColumn('avatar_path');
            }
        });
    }
};
