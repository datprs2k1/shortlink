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
        Schema::table('clicks', function (Blueprint $table) {
            // Remove the device_type index first
            $table->dropIndex(['device_type']);
            
            // Remove the device_type column as we use boolean flags instead
            $table->dropColumn('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            // Add the device_type column back
            $table->string('device_type')->nullable()->after('timezone');
            
            // Add the index back
            $table->index('device_type');
        });
    }
};