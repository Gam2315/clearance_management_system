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
        Schema::table('clearance_statuses', function (Blueprint $table) {
            $table->timestamp('cleared_at')->nullable()->after('status');
            $table->text('remarks')->nullable()->after('cleared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_statuses', function (Blueprint $table) {
            $table->dropColumn(['cleared_at', 'remarks']);
        });
    }
};
