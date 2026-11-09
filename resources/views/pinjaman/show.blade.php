@extends('layouts.app')

@section('title', 'Detail Pinjaman')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Detail Pengajuan Pinjaman</h3>
        </div>

        <div class="card-body">

            <table class="table table-bordered">
                <tr>
                    <th width="30%">Nama Anggota</th>
                    <td>{{ $pinjaman->anggota->nama }}</td>
                </tr>
                <tr>
                    <th>Jumlah Pengajuan</th>
                    <td>Rp {{ number_format((float) $pinjaman->jumlah_pengajuan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Jumlah Disetujui</th>
                    <td>
                        {{ $pinjaman->jumlah_disetujui !== null
                            ? 'Rp '.number_format((float) $pinjaman->jumlah_disetujui, 0, ',', '.')
                            : '-' }}
                    </td>
                </tr>
                <tr>
                    <th>Bunga</th>
                    <td>{{ $pinjaman->bunga_persen }}%</td>
                </tr>
                <tr>
                    <th>Tenor</th>
                    <td>{{ $pinjaman->tenor_bulan }} Bulan</td>
                </tr>
                <tr>
                    <th>Tujuan</th>
                    <td>{{ $pinjaman->tujuan }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge badge-primary">
                            {{ ucfirst($pinjaman->status) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Diproses Oleh</th>
                    <td>{{ $pinjaman->pengurus->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Waktu Persetujuan</th>
                    <td>{{ $pinjaman->approved_at?->format('d-m-Y H:i') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Catatan Persetujuan</th>
                    <td>{{ $pinjaman->catatan_persetujuan ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Waktu Pencairan</th>
                    <td>{{ $pinjaman->disbursed_at?->format('d-m-Y H:i') ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Waktu Pelunasan</th>
                    <td>{{ $pinjaman->lunas_at?->format('d-m-Y H:i') ?? '-' }}</td>
                </tr>
            </table>

            <hr>

            {{-- Persetujuan dan penolakan --}}
            @if($pinjaman->status === 'diajukan')
                <h5 class="text-success">Persetujuan Pengurus</h5>

                <form action="{{ route('pinjaman.approve', $pinjaman->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Jumlah Disetujui (Rp)</label>
                        <input type="number"
                               name="jumlah_disetujui"
                               class="form-control"
                               min="1"
                               max="{{ $pinjaman->jumlah_pengajuan }}"
                               step="0.01"
                               value="{{ $pinjaman->jumlah_pengajuan }}"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Catatan Persetujuan</label>
                        <textarea name="catatan_persetujuan"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>

                    <button class="btn btn-success"
                            onclick="return confirm('Setujui pinjaman ini?')">
                        <i class="fas fa-check"></i> Setujui Pinjaman
                    </button>
                </form>

                <hr>

                <h5 class="text-danger">Tolak Pengajuan</h5>

                <form action="{{ route('pinjaman.reject', $pinjaman->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Alasan Penolakan</label>
                        <textarea name="catatan_persetujuan"
                                  class="form-control"
                                  rows="2"
                                  required></textarea>
                    </div>

                    <button class="btn btn-danger"
                            onclick="return confirm('Tolak pengajuan ini?')">
                        <i class="fas fa-times"></i> Tolak Pinjaman
                    </button>
                </form>
            @endif

            {{-- Pencairan --}}
            @if($pinjaman->status === 'disetujui')
                <form action="{{ route('pinjaman.disburse', $pinjaman->id) }}" method="POST">
                    @csrf
                    <button class="btn btn-primary"
                            onclick="return confirm('Cairkan pinjaman ini?')">
                        <i class="fas fa-money-bill-wave"></i> Cairkan Pinjaman
                    </button>
                </form>
            @endif

            {{-- Pembatalan --}}
            @if(in_array($pinjaman->status, ['diajukan', 'disetujui']))
                <form action="{{ route('pinjaman.cancel', $pinjaman->id) }}"
                      method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-secondary"
                            onclick="return confirm('Batalkan pengajuan ini?')">
                        Batalkan Pinjaman
                    </button>
                </form>
            @endif

            <hr>

            {{-- Tautan ke jadwal angsuran --}}
            @if(in_array($pinjaman->status, ['dicairkan', 'lunas']))
                <a href="{{ route('angsuran.index', ['search' => $pinjaman->anggota->nama]) }}"
                   class="btn btn-info">
                    <i class="fas fa-calendar-alt"></i> Lihat Jadwal Angsuran
                </a>
            @endif

            <a href="{{ route('pinjaman.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection