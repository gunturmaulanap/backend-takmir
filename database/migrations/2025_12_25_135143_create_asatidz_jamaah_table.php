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
        Schema::create('asatidz_jamaah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asatidz_id')->constrained('asatidzs')->cascadeOnDelete();
            $table->foreignId('jamaah_id')->constrained('jamaahs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['asatidz_id', 'jamaah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asatidz_jamaah');
    }
};
