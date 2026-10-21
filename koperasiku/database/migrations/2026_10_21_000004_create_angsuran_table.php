<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('angsuran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pinjaman_id')
                  ->constrained('pinjaman')
                  ->restrictOnDelete();
            $table->unsignedSmallInteger('angsuran_ke');
            $table->date('jatuh_tempo');
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->date('tanggal_bayar')->nullable();
            $table->enum('status', [
                'belum_bayar', 'sebagian', 'lunas', 'terlambat'
            ])->default('belum_bayar');
            $table->timestamps();

            // satu pinjaman tidak boleh punya angsuran_ke ganda
            $table->unique(['pinjaman_id', 'angsuran_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsuran');
    }
};
