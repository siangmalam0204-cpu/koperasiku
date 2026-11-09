<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_angsurans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('angsuran_id')
                ->constrained('angsurans')
                ->restrictOnDelete();

            $table->string('kode_pembayaran', 50)->unique();
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);

            $table->foreignId('dibayar_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_angsurans');
    }
};