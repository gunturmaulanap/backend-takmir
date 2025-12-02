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
        Schema::create('jadwal_khutbahs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('imam_id')->nullable()->constrained('imams')->cascadeOnDelete();
            $table->foreignId('khatib_id')->nullable()->constrained('khatibs')->cascadeOnDelete();
            $table->foreignId('muadzin_id')->nullable()->constrained('muadzins')->cascadeOnDelete();
            $table->foreignId('profile_masjid_id')->constrained('profile_masjids')->cascadeOnDelete();
            $table->text('tema_khutbah')->nullable();
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
        Schema::dropIfExists('jadwal_khutbahs');
    }
};
