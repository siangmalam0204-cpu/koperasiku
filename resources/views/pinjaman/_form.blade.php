<div class="form-group">
    <label>Nama Anggota <span class="text-danger">*</span></label>
    <select name="anggota_id"
            class="form-control @error('anggota_id') is-invalid @enderror"
            required>
        <option value="">-- Pilih Anggota --</option>
        @foreach($anggotas as $anggota)
            <option value="{{ $anggota->id }}"
                @selected(old('anggota_id', $pinjaman->anggota_id ?? '') == $anggota->id)>
                {{ $anggota->nama }}
            </option>
        @endforeach
    </select>
    @error('anggota_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>Jumlah Pengajuan (Rp)</label>
    <input type="number"
           name="jumlah_pengajuan"
           class="form-control"
           min="1"
           step="0.01"
           value="{{ old('jumlah_pengajuan', $pinjaman->jumlah_pengajuan ?? '') }}"
           required>
    @error('jumlah_pengajuan')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Bunga per Tahun (%)</label>
            <input type="number"
                   name="bunga_persen"
                   class="form-control"
                   min="0"
                   max="100"
                   step="0.01"
                   value="{{ old('bunga_persen', $pinjaman->bunga_persen ?? 0) }}"
                   required>
            @error('bunga_persen')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Tenor (Bulan)</label>
            <input type="number"
                   name="tenor_bulan"
                   class="form-control"
                   min="1"
                   max="120"
                   value="{{ old('tenor_bulan', $pinjaman->tenor_bulan ?? '') }}"
                   required>
            @error('tenor_bulan')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Tujuan Pinjaman</label>
    <textarea name="tujuan"
              class="form-control"
              rows="4"
              required>{{ old('tujuan', $pinjaman->tujuan ?? '') }}</textarea>
    @error('tujuan')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>

<button type="submit" class="btn btn-primary">
    <i class="fas fa-save"></i> Simpan
</button>
<a href="{{ route('pinjaman.index') }}" class="btn btn-secondary">
    Kembali
</a>