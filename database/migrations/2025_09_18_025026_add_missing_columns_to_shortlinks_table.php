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
        Schema::table('shortlinks', function (Blueprint $table) {
            $table->text('description')->nullable()->after('original_url');
            $table->string('password')->nullable()->after('description');
            $table->json('tags')->nullable()->after('password');
            $table->unsignedBigInteger('clicks_count')->default(0)->after('tags');
            
            // Add indexes for performance
            $table->index('clicks_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shortlinks', function (Blueprint $table) {
            $table->dropIndex(['clicks_count']);
            $table->dropColumn(['description', 'password', 'tags', 'clicks_count']);
        });
    }
};