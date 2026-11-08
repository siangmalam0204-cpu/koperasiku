@extends('layouts.app')

@section('title', 'Manajemen Pinjaman')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">
        <div>
            <h3>Manajemen Pinjaman</h3>
            <p class="text-muted">Pengelolaan pengajuan pinjaman anggota koperasi</p>
        </div>

        <a href="{{ route('pinjaman.create') }}"
           class="btn btn-primary align-self-start">
            <i class="fas fa-plus"></i> Tambah Pinjaman
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPengajuan }}</h3>
                    <p>Total Pengajuan</p>
                </div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $menunggu }}</h3>
                    <p>Menunggu Persetujuan</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $disetujui }}</h3>
                    <p>Disetujui</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $dicairkan }}</h3>
                    <p>Sudah Dicairkan</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Pengajuan Pinjaman</h3>
        </div>

        <div class="card-body">

            <form method="GET" class="row mb-3">
                <div class="col-md-5 mb-2">
                    <input type="text" name="search" class="form-control"
                           placeholder="Cari nama anggota..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-4 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach(['diajukan', 'disetujui', 'ditolak', 'dicairkan', 'lunas', 'dibatalkan'] as $status)
                            <option value="{{ $status }}"
                                @selected(request('status') == $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('pinjaman.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Anggota</th>
                            <th>Jumlah Pengajuan</th>
                            <th>Jumlah Disetujui</th>
                            <th>Tenor</th>
                            <th>Bunga</th>
                            <th>Status</th>
                            <th width="190">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pinjamans as $index => $pinjaman)
                            <tr>
                                <td>{{ $pinjamans->firstItem() + $index }}</td>
                                <td><strong>{{ $pinjaman->anggota->nama }}</strong></td>
                                <td>Rp {{ number_format((float) $pinjaman->jumlah_pengajuan, 0, ',', '.') }}</td>
                                <td>
                                    @if($pinjaman->jumlah_disetujui !== null)
                                        Rp {{ number_format((float) $pinjaman->jumlah_disetujui, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $pinjaman->tenor_bulan }} Bulan</td>
                                <td>{{ $pinjaman->bunga_persen }}%</td>
                                <td>
                                    @php
                                        $colors = [
                                            'diajukan' => 'warning',
                                            'disetujui' => 'info',
                                            'ditolak' => 'danger',
                                            'dicairkan' => 'primary',
                                            'lunas' => 'success',
                                            'dibatalkan' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $colors[$pinjaman->status] ?? 'secondary' }}">
                                        {{ ucfirst($pinjaman->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('pinjaman.show', $pinjaman->id) }}"
                                       class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($pinjaman->status === 'diajukan')
                                        <a href="{{ route('pinjaman.edit', $pinjaman->id) }}"
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('pinjaman.destroy', $pinjaman->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Hapus pengajuan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    Belum ada data pinjaman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $pinjamans->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection