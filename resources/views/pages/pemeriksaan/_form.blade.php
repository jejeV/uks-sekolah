@php
    $p = $pemeriksaan ?? null;
    $selectedAnggota = old('anggota_id', $p->anggota_id ?? '');
    $selectedSemester = old('semester', $p->semester ?? '');
    $selectedTahun = old('tahun_ajaran', $p->tahun_ajaran ?? '');
    $beratBadan = old('berat_badan', $p->berat_badan ?? '');
    $tinggiBadan = old('tinggi_badan', $p->tinggi_badan ?? '');
    $penglihatanKiri = old('penglihatan_kiri', $p->penglihatan_kiri ?? '');
    $penglihatanKanan = old('penglihatan_kanan', $p->penglihatan_kanan ?? '');
    $pendengaran = old('pendengaran', $p->pendengaran ?? '');
    $kondisiGigi = old('kondisi_gigi', $p->kondisi_gigi ?? '');
    $catatan = old('catatan', $p->catatan ?? '');
@endphp

<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-6">
        <label for="anggota_id" class="form-label">Anggota</label>
        <select id="anggota_id" name="anggota_id" class="form-select" required>
            <option value="">Pilih anggota</option>
            @foreach ($anggota as $item)
                <option value="{{ $item->id }}" {{ $selectedAnggota == $item->id ? 'selected' : '' }}>
                    {{ $item->nama }} ({{ ucfirst($item->tipe) }} - {{ $item->jenjang->nama }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="semester" class="form-label">Semester</label>
        <select id="semester" name="semester" class="form-select" required>
            <option value="">Pilih semester</option>
            <option value="1" {{ $selectedSemester == '1' ? 'selected' : '' }}>1</option>
            <option value="2" {{ $selectedSemester == '2' ? 'selected' : '' }}>2</option>
        </select>
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
        <select id="tahun_ajaran" name="tahun_ajaran" class="form-select" required>
            <option value="">Pilih tahun</option>
            @foreach ($tahunOptions as $tahun)
                <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
        <input id="berat_badan" name="berat_badan" type="number" min="1" max="200" step="0.1" class="form-control" value="{{ $beratBadan }}" placeholder="70">
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
        <input id="tinggi_badan" name="tinggi_badan" type="number" min="50" max="250" step="0.1" class="form-control" value="{{ $tinggiBadan }}" placeholder="160">
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="penglihatan_kiri" class="form-label">Penglihatan Kiri</label>
        <input id="penglihatan_kiri" name="penglihatan_kiri" type="text" class="form-control" value="{{ $penglihatanKiri }}" placeholder="1.0 / 0.8">
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="penglihatan_kanan" class="form-label">Penglihatan Kanan</label>
        <input id="penglihatan_kanan" name="penglihatan_kanan" type="text" class="form-control" value="{{ $penglihatanKanan }}" placeholder="1.0 / 0.8">
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="pendengaran" class="form-label">Pendengaran</label>
        <select id="pendengaran" name="pendengaran" class="form-select">
            <option value="">Pilih kondisi</option>
            <option value="normal" {{ $pendengaran === 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="kurang" {{ $pendengaran === 'kurang' ? 'selected' : '' }}>Kurang</option>
            <option value="tuli" {{ $pendengaran === 'tuli' ? 'selected' : '' }}>Tuli</option>
        </select>
    </div>

    <div class="col-span-6 sm:col-span-3">
        <label for="kondisi_gigi" class="form-label">Kondisi Gigi</label>
        <select id="kondisi_gigi" name="kondisi_gigi" class="form-select">
            <option value="">Pilih kondisi</option>
            <option value="baik" {{ $kondisiGigi === 'baik' ? 'selected' : '' }}>Baik</option>
            <option value="caries" {{ $kondisiGigi === 'caries' ? 'selected' : '' }}>Caries</option>
            <option value="perlu_perawatan" {{ $kondisiGigi === 'perlu_perawatan' ? 'selected' : '' }}>Perlu Perawatan</option>
        </select>
    </div>

    <div class="col-span-12">
        <label for="catatan" class="form-label">Catatan</label>
        <textarea id="catatan" name="catatan" class="form-control" rows="3" placeholder="Catatan tambahan...">{{ $catatan }}</textarea>
    </div>
</div>
