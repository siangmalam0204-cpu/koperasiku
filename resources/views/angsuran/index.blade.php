@extends('layouts.app')

@section('title', 'Manajemen Angsuran')
@section('page-title', 'Manajemen Angsuran')

@section('content')
<div class="container-fluid">

    <div class="mb-4">
        <h3>Manajemen Angsuran</h3>
        <p class="text-muted">
            Pengelolaan pembayaran angsuran pinjaman anggota.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                    <p>Total Tagihan</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalDibayar, 0, ',', '.') }}</h3>
                    <p>Total Pembayaran</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $belumBayar + $sebagian }}</h3>
                    <p>Belum Lunas</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $tunggakan }}</h3>
                    <p>Angsuran Menunggak</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabel Angsuran --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Daftar Angsuran Anggota</h3>
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
                        <option value="belum_bayar" @selected(request('status') == 'belum_bayar')>
                            Belum Bayar
                        </option>
                        <option value="sebagian" @selected(request('status') == 'sebagian')>
                            Dibayar Sebagian
                        </option>
                        <option value="lunas" @selected(request('status') == 'lunas')>
                            Lunas
                        </option>
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <a href="{{ route('angsuran.index') }}" class="btn btn-secondary">
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
                            <th>Angsuran Ke</th>
                            <th>Jatuh Tempo</th>
                            <th>Pokok</th>
                            <th>Bunga</th>
                            <th>Total Tagihan</th>
                            <th>Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($angsurans as $index => $angsuran)
                            @php
                                $sisa = max(
                                    0,
                                    (float) $angsuran->total_tagihan -
                                    (float) $angsuran->total_dibayar
                                );

                                $terlambat = $angsuran->status !== 'lunas'
                                    && $angsuran->jatuh_tempo->isBefore(today());
                            @endphp
                            <tr>
                                <td>{{ $angsurans->firstItem() + $index }}</td>
                                <td>{{ $angsuran->pinjaman->anggota->nama }}</td>
                                <td>Ke-{{ $angsuran->angsuran_ke }}</td>
                                <td>
                                    {{ $angsuran->jatuh_tempo->format('d-m-Y') }}
                                    @if($terlambat)
                                        <br>
                                        <span class="badge badge-danger">Terlambat</span>
                                    @endif
                                </td>
                                <td>{{ number_format($angsuran->pokok, 0, ',', '.') }}</td>
                                <td>{{ number_format($angsuran->bunga, 0, ',', '.') }}</td>
                                <td>{{ number_format($angsuran->total_tagihan, 0, ',', '.') }}</td>
                                <td>{{ number_format($angsuran->total_dibayar, 0, ',', '.') }}</td>
                                <td><strong>{{ number_format($sisa, 0, ',', '.') }}</strong></td>
                                <td>
                                    @if($angsuran->status === 'lunas')
                                        <span class="badge badge-success">Lunas</span>
                                    @elseif($angsuran->status === 'sebagian')
                                        <span class="badge badge-info">Sebagian</span>
                                    @else
                                        <span class="badge badge-warning">Belum Bayar</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('angsuran.show', $angsuran->id) }}"
                                       class="btn btn-info btn-sm" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">
                                    Belum ada data angsuran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $angsurans->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection