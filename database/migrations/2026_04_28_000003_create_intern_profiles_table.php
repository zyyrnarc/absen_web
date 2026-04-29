<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intern_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('student_id')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('major')->nullable();
            $table->string('division')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->date('internship_start')->nullable();
            $table->date('internship_end')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intern_profiles');
    }
};
