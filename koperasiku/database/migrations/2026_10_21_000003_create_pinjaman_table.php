<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')
                  ->constrained('anggota')
                  ->restrictOnDelete();
            $table->decimal('jumlah_pengajuan', 15, 2);
            $table->decimal('jumlah_disetujui', 15, 2)->nullable();
            $table->decimal('bunga_persen', 5, 2)->default(0);
            $table->unsignedSmallInteger('tenor_bulan');
            $table->enum('status', [
                'diajukan', 'disetujui', 'ditolak',
                'dicairkan', 'lunas'
            ])->default('diajukan');
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
