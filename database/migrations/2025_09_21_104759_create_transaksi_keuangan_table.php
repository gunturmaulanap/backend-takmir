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
        Schema::create('transaksi_keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_masjid_id')->constrained('profile_masjids')->onDelete('cascade');
            $table->enum('type', ['income', 'expense'])->comment('income = pemasukan, expense = pengeluaran');
            $table->string('kategori', 100);
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->string('bukti_transaksi', 255)->nullable()->comment('foto struk/nota');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Index untuk optimasi query
            $table->index(['profile_masjid_id', 'type']);
            $table->index(['profile_masjid_id', 'tanggal']);
            $table->index(['tanggal', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_keuangan');
    }
};
