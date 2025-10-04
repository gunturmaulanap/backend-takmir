
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
        Schema::create('jamaahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_masjid_id')->constrained('profile_masjids')->cascadeOnDelete();
            $table->string('slug');
            $table->unique(['slug', 'profile_masjid_id']);
            $table->string('nama');
            $table->string('no_handphone');
            $table->text('alamat');
            $table->integer('umur');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('aktivitas_jamaah')->nullable();
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
        Schema::dropIfExists('jamaahs');
    }
};
