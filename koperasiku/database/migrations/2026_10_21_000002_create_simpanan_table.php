<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('simpanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')
                  ->constrained('anggota')
                  ->restrictOnDelete();
            $table->enum('jenis', [
                'pokok', 'wajib', 'sukarela'
            ]);
            $table->decimal('nominal', 15, 2);
            $table->dateTime('tanggal_transaksi');
            $table->string('status')->default('menunggu');
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpanan');
    }
};
