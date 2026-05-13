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
            if (! Schema::hasColumn('users', 'nim')) {
                $table->string('nim')->nullable();
            }

            if (! Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable();
            }

            if (! Schema::hasColumn('users', 'study_program')) {
                $table->string('study_program')->nullable();
            }

            if (! Schema::hasColumn('users', 'industry_name')) {
                $table->string('industry_name')->nullable();
            }

            if (! Schema::hasColumn('users', 'mentor_name')) {
                $table->string('mentor_name')->nullable();
            }

            if (! Schema::hasColumn('users', 'mentor_position')) {
                $table->string('mentor_position')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('users', function (Blueprint $table) {
            foreach ([
                'mentor_position',
                'mentor_name',
                'industry_name',
                'study_program',
                'department',
                'nim',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
