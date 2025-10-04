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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreignId('profile_masjid_id')->constrained('profile_masjids')->cascadeOnDelete();
            $table->string('nama');
            $table->string('slug');
            $table->unique(['slug', 'profile_masjid_id']);
            $table->date('tanggal_event');
            $table->string('waktu_event');
            $table->string('tempat_event');
            $table->text('deskripsi');
            $table->string('image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
