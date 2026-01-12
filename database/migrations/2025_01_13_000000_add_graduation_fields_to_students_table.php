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
        Schema::table('students', function (Blueprint $table) {
            // Graduation status fields
            $table->boolean('is_graduating')->default(false)->after('is_graduated');
            $table->string('graduation_semester')->nullable()->after('is_graduating'); // '1st', '2nd'
            $table->unsignedBigInteger('graduation_academic_year_id')->nullable()->after('graduation_semester');
            $table->date('expected_graduation_date')->nullable()->after('graduation_academic_year_id');
            $table->date('actual_graduation_date')->nullable()->after('expected_graduation_date');
            $table->text('graduation_requirements')->nullable()->after('actual_graduation_date'); // JSON field for tracking requirements
            $table->boolean('graduation_clearance_completed')->default(false)->after('graduation_requirements');
            $table->timestamp('graduation_clearance_completed_at')->nullable()->after('graduation_clearance_completed');
            
            // Foreign key constraint
            $table->foreign('graduation_academic_year_id')->references('id')->on('academic_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['graduation_academic_year_id']);
            $table->dropColumn([
                'is_graduating',
                'graduation_semester',
                'graduation_academic_year_id',
                'expected_graduation_date',
                'actual_graduation_date',
                'graduation_requirements',
                'graduation_clearance_completed',
                'graduation_clearance_completed_at'
            ]);
        });
    }
};
