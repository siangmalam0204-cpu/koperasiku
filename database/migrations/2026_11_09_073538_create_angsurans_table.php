<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angsurans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pinjaman_id')
                ->constrained('pinjamans')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('angsuran_ke');
            $table->date('jatuh_tempo');
            $table->decimal('pokok', 15, 2);
            $table->decimal('bunga', 15, 2);
            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('total_dibayar', 15, 2)->default(0);
            $table->string('status', 20)->default('belum_bayar');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique(['pinjaman_id', 'angsuran_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angsurans');
    }
};