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
        Schema::table('users', function (Blueprint $table) {

            $table->string('nim')->nullable();

            $table->string('department')->nullable();

            $table->string('study_program')->nullable();

            $table->string('industry_name')->nullable();

            $table->string('mentor_name')->nullable();

            $table->string('mentor_position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'nim',
                'department',
                'study_program',
                'industry_name',
                'mentor_name',
                'mentor_position',
            ]);
        });
    }
};
