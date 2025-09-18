<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->string('country')->nullable()->after('country_code');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->nullable()->after('region');
            $table->string('postal_code')->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('postal_code');
            $table->decimal('longitude', 11, 7)->nullable()->after('latitude');
            $table->string('timezone')->nullable()->after('longitude');
            $table->string('device_type')->nullable()->after('timezone');
            $table->string('browser')->nullable()->after('device_type');
            $table->string('operating_system')->nullable()->after('browser');
            $table->string('referrer_url', 2048)->nullable()->after('operating_system');
            
            // Add indexes for commonly queried fields
            $table->index('country');
            $table->index('city');
            $table->index('device_type');
        });
    }

    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropIndex(['country']);
            $table->dropIndex(['city']);
            $table->dropIndex(['device_type']);
            
            $table->dropColumn([
                'country',
                'region', 
                'city',
                'postal_code',
                'latitude',
                'longitude',
                'timezone',
                'device_type',
                'browser',
                'operating_system',
                'referrer_url'
            ]);
        });
    }
};
