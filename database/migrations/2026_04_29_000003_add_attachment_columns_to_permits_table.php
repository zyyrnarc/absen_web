<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            if (! Schema::hasColumn('permits', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('reason');
            }

            if (! Schema::hasColumn('permits', 'attachment_original_name')) {
                $table->string('attachment_original_name')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $drops = [];

            foreach (['attachment_path', 'attachment_original_name'] as $column) {
                if (Schema::hasColumn('permits', $column)) {
                    $drops[] = $column;
                }
            }

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
