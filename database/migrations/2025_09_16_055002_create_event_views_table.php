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
        Schema::create('event_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_masjid_id')->constrained('profile_masjids')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->cascadeOnDelete();
            $table->foreignId('jadwal_khutbah_id')->nullable()->constrained('jadwal_khutbahs')->cascadeOnDelete();
            $table->string('title'); // Nama event atau tema khutbah
            $table->date('tanggal'); // Tanggal event atau jadwal khutbah
            $table->time('waktu')->nullable(); // Waktu event
            $table->enum('type', ['event', 'jadwal_khutbah']); // Tipe untuk membedakan
            $table->text('description')->nullable(); // Deskripsi
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Index untuk performa query
            $table->index(['profile_masjid_id', 'tanggal', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_views');
    }
};
