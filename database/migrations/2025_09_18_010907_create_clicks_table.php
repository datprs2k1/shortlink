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
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shortlink_id')->constrained('shortlinks')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->string('referer', 2048)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();

            // Indexes
            $table->index('shortlink_id');
            $table->index('clicked_at');
            $table->index('ip_address');
            $table->index('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
