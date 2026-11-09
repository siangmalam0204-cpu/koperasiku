<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranAngsuran extends Model
{
    protected $fillable = [
        'angsuran_id',
        'kode_pembayaran',
        'tanggal_bayar',
        'jumlah_bayar',
        'dibayar_oleh',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar' => 'date',
            'jumlah_bayar' => 'decimal:2',
        ];
    }

    public function angsuran(): BelongsTo
    {
        return $this->belongsTo(Angsuran::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }
}