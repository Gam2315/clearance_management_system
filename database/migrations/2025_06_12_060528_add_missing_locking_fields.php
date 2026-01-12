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
        Schema::table('clearances', function (Blueprint $table) {
            if (!Schema::hasColumn('clearances', 'can_unlock_roles')) {
                $table->json('can_unlock_roles')->nullable()->after('locked_by');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'has_locked_clearance')) {
                $table->boolean('has_locked_clearance')->default(false)->after('is_uniwide');
            }
            if (!Schema::hasColumn('students', 'locked_academic_years')) {
                $table->json('locked_academic_years')->nullable()->after('has_locked_clearance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearances', function (Blueprint $table) {
            if (Schema::hasColumn('clearances', 'can_unlock_roles')) {
                $table->dropColumn('can_unlock_roles');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'has_locked_clearance')) {
                $table->dropColumn('has_locked_clearance');
            }
            if (Schema::hasColumn('students', 'locked_academic_years')) {
                $table->dropColumn('locked_academic_years');
            }
        });
    }
};
