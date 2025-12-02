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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('refresh_token', 255)->unique();
            $table->timestamp('expires_at');
            $table->string('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('revoked')->default(false);
            $table->timestamps();

            // Index untuk token lookup (saat refresh)
            $table->index('refresh_token', 'idx_refresh_token');

            // Index untuk user lookup (saat limit tokens & cleanup)
            $table->index(['user_id', 'revoked'], 'idx_user_revoked');

            // Composite index untuk cleanup expired tokens
            $table->index(['revoked', 'expires_at'], 'idx_revoked_expired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
