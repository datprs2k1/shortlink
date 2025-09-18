<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->string('browser_version')->nullable()->after('browser');
            $table->string('operating_system_version')->nullable()->after('operating_system');
            $table->boolean('is_mobile')->default(false)->after('operating_system_version');
            $table->boolean('is_tablet')->default(false)->after('is_mobile');
            $table->boolean('is_desktop')->default(false)->after('is_tablet');
            $table->boolean('is_robot')->default(false)->after('is_desktop');
        });
    }

    public function down(): void
    {
        Schema::table('clicks', function (Blueprint $table) {
            $table->dropColumn([
                'browser_version',
                'operating_system_version',
                'is_mobile',
                'is_tablet',
                'is_desktop',
                'is_robot'
            ]);
        });
    }
};