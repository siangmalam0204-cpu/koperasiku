<?php

namespace App\Http\Controllers;

use App\Models\Pinjaman;
use App\Models\Anggota;
use App\Services\AngsuranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PinjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pinjaman::with(['anggota', 'pengurus'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pinjamans = $query->paginate(10)->withQueryString();

        $totalPengajuan = Pinjaman::count();
        $menunggu = Pinjaman::where('status', 'diajukan')->count();
        $disetujui = Pinjaman::where('status', 'disetujui')->count();
        $dicairkan = Pinjaman::where('status', 'dicairkan')->count();

        return view('pinjaman.index', compact(
            'pinjamans',
            'totalPengajuan',
            'menunggu',
            'disetujui',
            'dicairkan'
        ));
    }

    public function create()
    {
        $anggotas = Anggota::orderBy('nama')->get();

        return view('pinjaman.create', compact('anggotas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id' => ['required', 'exists:anggota,id'],
            'jumlah_pengajuan' => ['required', 'numeric', 'min:1'],
            'bunga_persen' => ['required', 'numeric', 'min:0', 'max:100'],
            'tenor_bulan' => ['required', 'integer', 'min:1', 'max:120'],
            'tujuan' => ['required', 'string', 'max:2000'],
        ]);

        $validated['status'] = 'diajukan';

        Pinjaman::create($validated);

        return redirect()
            ->route('pinjaman.index')
            ->with('success', 'Pengajuan pinjaman berhasil disimpan.');
    }

    public function show(Pinjaman $pinjaman)
    {
        $pinjaman->load(['anggota', 'pengurus']);

        return view('pinjaman.show', compact('pinjaman'));
    }

    public function edit(Pinjaman $pinjaman)
    {
        abort_unless($pinjaman->status === 'diajukan', 403,
            'Hanya pengajuan yang belum diproses yang dapat diubah.');

        $anggotas = Anggota::orderBy('nama')->get();

        return view('pinjaman.edit', compact('pinjaman', 'anggotas'));
    }

    public function update(Request $request, Pinjaman $pinjaman)
    {
        abort_unless($pinjaman->status === 'diajukan', 403,
            'Pengajuan yang sudah diproses tidak dapat diubah.');

        $validated = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'jumlah_pengajuan' => 'required|numeric|min:1',
            'bunga_persen' => 'required|numeric|min:0|max:100',
            'tenor_bulan' => 'required|integer|min:1|max:120',
            'tujuan' => 'required|string|max:2000',
        ]);

        $pinjaman->update($validated);

        return redirect()
            ->route('pinjaman.index')
            ->with('success', 'Data pinjaman berhasil diperbarui.');
    }

    public function destroy(Pinjaman $pinjaman)
    {
        abort_unless($pinjaman->status === 'diajukan', 403,
            'Pinjaman yang sudah diproses tidak dapat dihapus.');

        $pinjaman->delete();

        return back()->with('success', 'Pengajuan pinjaman berhasil dihapus.');
    }

    /**
     * Menyetujui pengajuan.
     */
    public function approve(Request $request, Pinjaman $pinjaman)
    {
        $validated = $request->validate([
            'jumlah_disetujui' => 'required|numeric|min:1',
            'catatan_persetujuan' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            if ((float) $validated['jumlah_disetujui'] > (float) $data->jumlah_pengajuan) {
                throw ValidationException::withMessages([
                    'jumlah_disetujui' =>
                        'Jumlah yang disetujui tidak boleh melebihi pengajuan.',
                ]);
            }

            $data->update([
                'jumlah_disetujui' => $validated['jumlah_disetujui'],
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'catatan_persetujuan' =>
                    $validated['catatan_persetujuan'] ?? null,
            ]);
        });

        return back()->with('success', 'Pinjaman berhasil disetujui.');
    }

    /**
     * Menolak pengajuan.
     */
    public function reject(Request $request, Pinjaman $pinjaman)
    {
        $validated = $request->validate([
            'catatan_persetujuan' => 'required|string|max:2000',
        ]);

        DB::transaction(function () use ($pinjaman, $validated) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'diajukan') {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini sudah diproses.',
                ]);
            }

            $data->update([
                'status' => 'ditolak',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'catatan_persetujuan' => $validated['catatan_persetujuan'],
            ]);
        });

        return back()->with('success', 'Pengajuan pinjaman ditolak.');
    }

    /**
     * Mencairkan pinjaman yang telah disetujui,
     * sekaligus membuat jadwal angsuran.
     */
    public function disburse(
        Pinjaman $pinjaman,
        AngsuranService $angsuranService
    ) {
        DB::transaction(function () use ($pinjaman, $angsuranService) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data->status !== 'disetujui') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pinjaman disetujui yang dapat dicairkan.',
                ]);
            }

            // Buat seluruh jadwal angsuran
            $angsuranService->generate($data);

            // Ubah status pinjaman
            $data->update([
                'status' => 'dicairkan',
                'disbursed_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Pinjaman berhasil dicairkan dan jadwal angsuran dibuat.'
        );
    }

    /**
     * Membatalkan pengajuan yang belum dicairkan.
     */
    public function cancel(Pinjaman $pinjaman)
    {
        DB::transaction(function () use ($pinjaman) {
            $data = Pinjaman::whereKey($pinjaman->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($data->status, ['diajukan', 'disetujui'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pinjaman ini tidak dapat dibatalkan.',
                ]);
            }

            $data->update([
                'status' => 'dibatalkan',
            ]);
        });

        return back()->with('success', 'Pinjaman berhasil dibatalkan.');
    }
}