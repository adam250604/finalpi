<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify any existing records to match new status values
        DB::table('appointments')
            ->where('status', 'scheduled')
            ->update(['status' => 'pending']);

        // Then modify the enum
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to original values
        DB::table('appointments')
            ->where('status', 'pending')
            ->update(['status' => 'scheduled']);

        // Revert the enum
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default('scheduled')->change();
        });
    }
};
