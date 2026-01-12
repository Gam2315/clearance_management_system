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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->constrained('users')->onDelete('cascade');
            $table->string('student_number')->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->string('year')->nullable();
            $table->foreignId('academic_id')->nullable()->constrained('academic_years')->onDelete('cascade');
            $table->string('nfc_uid')->nullable()->unique();
            $table->boolean('is_archived')->default(false);
            $table->boolean('has_violations')->default(false);
            $table->boolean('is_graduated')->default(false);
            $table->json('clearance_history')->nullable(); // Track clearance completion by semester
            $table->boolean('first_year_clearance_completed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
