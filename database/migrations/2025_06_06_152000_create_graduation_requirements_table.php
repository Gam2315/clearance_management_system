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
        Schema::create('graduation_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->string('requirement_type'); // 'clearance', 'thesis', 'ojt', 'grades', 'fees', 'documents'
            $table->string('requirement_name');
            $table->text('requirement_description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'waived'])->default('pending');
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable(); // User who marked as completed
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data like file paths, scores, etc.
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['requirement_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_requirements');
    }
};
