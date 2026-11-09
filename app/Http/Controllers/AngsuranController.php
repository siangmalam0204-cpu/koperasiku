<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\PembayaranAngsuran;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AngsuranController extends Controller
{
    /**
     * Daftar angsuran.
     */
    public function index(Request $request)
    {
        $query = Angsuran::with('pinjaman.anggota')
            ->latest('jatuh_tempo');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;

            $query->whereHas('pinjaman.anggota', function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%");
            });
        }

        $angsurans = $query->paginate(10)->withQueryString();

        $totalTagihan = Angsuran::sum('total_tagihan');
        $totalDibayar = Angsuran::sum('total_dibayar');
        $belumBayar = Angsuran::where('status', 'belum_bayar')->count();
        $sebagian = Angsuran::where('status', 'sebagian')->count();
        $lunas = Angsuran::where('status', 'lunas')->count();

        $tunggakan = Angsuran::whereDate('jatuh_tempo', '<', today())
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->count();

        return view('angsuran.index', compact(
            'angsurans',
            'totalTagihan',
            'totalDibayar',
            'belumBayar',
            'sebagian',
            'lunas',
            'tunggakan'
        ));
    }

    /**
     * Detail angsuran dan riwayat pembayaran.
     */
    public function show(Angsuran $angsuran)
    {
        $angsuran->load([
            'pinjaman.anggota',
            'pembayarans.petugas',
        ]);

        return view('angsuran.show', compact('angsuran'));
    }

    /**
     * Menyimpan pembayaran angsuran.
     */
    public function pay(Request $request, Angsuran $angsuran)
    {
        $validated = $request->validate([
            'jumlah_bayar' => 'required|numeric|min:0.01',
            'tanggal_bayar' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($angsuran, $validated) {

            $data = Angsuran::whereKey($angsuran->id)
                ->lockForUpdate()
                ->firstOrFail();

            $pinjaman = Pinjaman::whereKey($data->pinjaman_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pinjaman->status !== 'dicairkan') {
                throw ValidationException::withMessages([
                    'angsuran' => 'Pinjaman tidak dalam status dapat diangsur.',
                ]);
            }

            if ($data->status === 'lunas') {
                throw ValidationException::withMessages([
                    'angsuran' => 'Angsuran ini sudah lunas.',
                ]);
            }

            // Konversi ke satuan sen
            $tagihan = (int) round((float) $data->total_tagihan * 100);
            $sudahDibayar = (int) round((float) $data->total_dibayar * 100);
            $pembayaran = (int) round((float) $validated['jumlah_bayar'] * 100);

            $sisa = $tagihan - $sudahDibayar;

            if ($pembayaran <= 0 || $pembayaran > $sisa) {
                throw ValidationException::withMessages([
                    'jumlah_bayar' => 'Jumlah pembayaran melebihi sisa tagihan.',
                ]);
            }

            // Simpan transaksi pembayaran
            PembayaranAngsuran::create([
                'angsuran_id' => $data->id,
                'kode_pembayaran' => 'BYR-' . strtoupper((string) Str::uuid()),
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'jumlah_bayar' => $pembayaran / 100,
                'dibayar_oleh' => auth()->id(),
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $totalBaru = $sudahDibayar + $pembayaran;
            $lunas = $totalBaru >= $tagihan;

            // Update status angsuran
            $data->update([
                'total_dibayar' => $totalBaru / 100,
                'status' => $lunas ? 'lunas' : 'sebagian',
                'paid_at' => $lunas ? now() : null,
            ]);

            // Jika semua angsuran sudah lunas, pinjaman ikut lunas
            $masihAdaSisa = Angsuran::where('pinjaman_id', $pinjaman->id)
                ->where('status', '!=', 'lunas')
                ->exists();

            if (! $masihAdaSisa) {
                $pinjaman->update([
                    'status' => 'lunas',
                    'lunas_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('angsuran.show', $angsuran->id)
            ->with('success', 'Pembayaran angsuran berhasil disimpan.');
    }
}