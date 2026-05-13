<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('intern_activities', 'start_time')) {
                $table->time('start_time')->nullable()->after('activity_date');
            }

            if (! Schema::hasColumn('intern_activities', 'end_time')) {
                $table->time('end_time')->nullable()->after('start_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intern_activities', function (Blueprint $table) {
            foreach (['end_time', 'start_time'] as $column) {
                if (Schema::hasColumn('intern_activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
