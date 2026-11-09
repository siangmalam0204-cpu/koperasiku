<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Angsuran extends Model
{
    protected $fillable = [
        'pinjaman_id',
        'angsuran_ke',
        'jatuh_tempo',
        'pokok',
        'bunga',
        'total_tagihan',
        'total_dibayar',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'jatuh_tempo' => 'date',
            'pokok' => 'decimal:2',
            'bunga' => 'decimal:2',
            'total_tagihan' => 'decimal:2',
            'total_dibayar' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(PembayaranAngsuran::class, 'angsuran_id');
    }

    public function getSisaTagihanAttribute(): float
    {
        return max(
            0,
            (float) $this->total_tagihan - (float) $this->total_dibayar
        );
    }
}