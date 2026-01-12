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
        Schema::create('clearance_statuses', function (Blueprint $table) {
            $table->id();
             $table->foreignId('clearance_id')->nullable()->constrained('clearances')->onDelete('cascade');
            // Optional: reference to departments table
             $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->text('or_number')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_archived')->default(false);
               $table->boolean('can_submit')->default(true);
            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearance_statuses');
    }
};
