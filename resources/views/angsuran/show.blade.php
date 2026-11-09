@extends('layouts.app')

@section('title', 'Detail Angsuran')
@section('page-title', 'Detail Angsuran')

@section('content')
<div class="container-fluid">

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Detail Angsuran Pinjaman</h3>
        </div>

        <div class="card-body">

            <h5>Informasi Anggota</h5>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Nama Anggota</th>
                    <td>{{ $angsuran->pinjaman->anggota->nama }}</td>
                </tr>
                <tr>
                    <th>Nomor Pinjaman</th>
                    <td>{{ $angsuran->pinjaman->id }}</td>
                </tr>
                <tr>
                    <th>Angsuran Ke</th>
                    <td>{{ $angsuran->angsuran_ke }}</td>
                </tr>
                <tr>
                    <th>Jatuh Tempo</th>
                    <td>{{ $angsuran->jatuh_tempo->format('d-m-Y') }}</td>
                </tr>
            </table>

            <h5 class="mt-4">Informasi Tagihan</h5>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Pokok</th>
                    <td>Rp {{ number_format($angsuran->pokok, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Bunga</th>
                    <td>Rp {{ number_format($angsuran->bunga, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Total Tagihan</th>
                    <td>
                        <strong>Rp {{ number_format($angsuran->total_tagihan, 2, ',', '.') }}</strong>
                    </td>
                </tr>
                <tr>
                    <th>Total Dibayar</th>
                    <td>Rp {{ number_format($angsuran->total_dibayar, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Sisa Tagihan</th>
                    <td class="text-danger">
                        <strong>Rp {{ number_format($angsuran->sisa_tagihan, 2, ',', '.') }}</strong>
                    </td>
                </tr>
            </table>

            {{-- Form Pembayaran --}}
            @if($angsuran->status !== 'lunas'
                && $angsuran->pinjaman->status === 'dicairkan')
                <div class="card card-success card-outline mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Form Pembayaran Angsuran</h3>
                    </div>

                    <form action="{{ route('angsuran.pay', $angsuran->id) }}" method="POST">
                        @csrf

                        <div class="card-body">
                            <div class="form-group">
                                <label>Jumlah Pembayaran (Rp)</label>
                                <input type="number"
                                       name="jumlah_bayar"
                                       class="form-control"
                                       min="0.01"
                                       max="{{ $angsuran->sisa_tagihan }}"
                                       step="0.01"
                                       value="{{ old('jumlah_bayar', $angsuran->sisa_tagihan) }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label>Tanggal Pembayaran</label>
                                <input type="date"
                                       name="tanggal_bayar"
                                       class="form-control"
                                       value="{{ old('tanggal_bayar', today()->format('Y-m-d')) }}"
                                       max="{{ today()->format('Y-m-d') }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan"
                                          class="form-control"
                                          rows="2">{{ old('keterangan') }}</textarea>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit"
                                    class="btn btn-success"
                                    onclick="return confirm('Simpan pembayaran ini?')">
                                <i class="fas fa-money-bill-wave"></i>
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Riwayat Pembayaran --}}
            <div class="card card-outline card-info mt-4">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pembayaran</h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Pembayaran</th>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Petugas</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($angsuran->pembayarans as $index => $pembayaran)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $pembayaran->kode_pembayaran }}</td>
                                        <td>{{ $pembayaran->tanggal_bayar->format('d-m-Y') }}</td>
                                        <td>Rp {{ number_format($pembayaran->jumlah_bayar, 2, ',', '.') }}</td>
                                        <td>{{ $pembayaran->petugas->name ?? '-' }}</td>
                                        <td>{{ $pembayaran->keterangan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            Belum ada pembayaran.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="{{ route('angsuran.index') }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection