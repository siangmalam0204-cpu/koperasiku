<?php

namespace App\Services;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AngsuranService
{
    public function generate(Pinjaman $pinjaman): void
    {
        DB::transaction(function () use ($pinjaman) {

            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'disetujui') {
                throw ValidationException::withMessages([
                    'status' => 'Pinjaman harus disetujui sebelum pencairan.',
                ]);
            }

            if ((float) $data->jumlah_disetujui <= 0) {
                throw ValidationException::withMessages([
                    'jumlah_disetujui' => 'Jumlah pinjaman tidak valid.',
                ]);
            }

            if ($data->angsurans()->exists()) {
                throw ValidationException::withMessages([
                    'angsuran' => 'Jadwal angsuran sudah pernah dibuat.',
                ]);
            }

            // Semua hitungan dalam satuan sen (integer) agar tidak ada selisih pembulatan
            $jumlah = (int) round((float) $data->jumlah_disetujui * 100);
            $tenor = (int) $data->tenor_bulan;
            $bungaTahunan = (float) $data->bunga_persen;

            // Bunga flat per bulan (dalam sen)
            $bunga = (int) round($jumlah * $bungaTahunan / 100 / 12);

            // Pokok per bulan (dalam sen)
            $pokokDasar = intdiv($jumlah, $tenor);
            $sisaPokok = $jumlah;

            // Jatuh tempo pertama: satu bulan setelah pencairan
            $tanggalAwal = now()->startOfDay();

            for ($i = 1; $i <= $tenor; $i++) {

                // Angsuran terakhir menyerap sisa pembulatan
                $pokok = $i === $tenor ? $sisaPokok : $pokokDasar;
                $sisaPokok -= $pokok;

                $total = $pokok + $bunga;

                $jatuhTempo = $tanggalAwal->copy()->addMonthsNoOverflow($i);

                Angsuran::create([
                    'pinjaman_id' => $data->id,
                    'angsuran_ke' => $i,
                    'jatuh_tempo' => $jatuhTempo,
                    'pokok' => $pokok / 100,
                    'bunga' => $bunga / 100,
                    'total_tagihan' => $total / 100,
                    'total_dibayar' => 0,
                    'status' => 'belum_bayar',
                ]);
            }
        });
    }
}