{{--
    Form raport kesehatan, dipakai halaman create/edit dan modal.
    Parameter: $pemeriksaan (edit), $selectedAnggota, $modalId, $pickerTipe, $fixedAnggota (bool), $tahunOptions (edit)
--}}
@php
    $p = $pemeriksaan ?? null;
    $isEdit = (bool) $p;
    $modalId = $modalId ?? null;
    $useOld = old('_modal') === $modalId;
    $value = fn ($key) => $useOld ? old($key, optional($p)->$key) : optional($p)->$key;
    $prefix = $modalId ? $modalId . '-' : '';
    $semesterBerjalan = \App\Support\TahunAjaran::semester();
    $tahunAjaranBerjalan = \App\Support\TahunAjaran::tahun();
    $selectedAnggota = $selectedAnggota ?? null;
    $isGuru = ($useOld ? old('anggota_tipe', optional($selectedAnggota)->tipe) : optional($selectedAnggota)->tipe) === 'guru';
@endphp

@if ($modalId)
    <input type="hidden" name="_modal" value="{{ $modalId }}">
@endif

<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 {{ $isEdit ? 'lg:col-span-6' : '' }}">
        @if ($fixedAnggota ?? false)
            <input type="hidden" name="anggota_id" value="{{ $selectedAnggota->id }}" data-anggota-id data-tipe="{{ $selectedAnggota->tipe }}">
            <div class="form-label">Siswa / Guru</div>
            <div class="font-medium">{{ $selectedAnggota->picker_label }}</div>
        @else
            @include('components.anggota-picker', [
                'pickerId' => $prefix . 'anggota',
                'tipe'     => $pickerTipe ?? 'pemeriksaan',
                'selected' => $selectedAnggota,
                'useOld'   => $useOld,
            ])
        @endif
    </div>

    @if ($isEdit)
        <div class="col-span-6 lg:col-span-3">
            <label for="{{ $prefix }}semester" class="form-label">Semester</label>
            <select id="{{ $prefix }}semester" name="semester" class="form-select" required>
                @foreach ([1, 2] as $semester)
                    <option value="{{ $semester }}" @if ((int) $value('semester') === $semester) selected @endif>Semester {{ $semester }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-span-6 lg:col-span-3">
            <label for="{{ $prefix }}tahun_ajaran" class="form-label">Tahun Ajaran</label>
            <select id="{{ $prefix }}tahun_ajaran" name="tahun_ajaran" class="form-select" required>
                @foreach (collect($tahunOptions)->push((int) $p->tahun_ajaran)->unique()->sortDesc() as $tahun)
                    <option value="{{ $tahun }}" @if ((int) $value('tahun_ajaran') === (int) $tahun) selected @endif>{{ $tahun }}/{{ $tahun + 1 }}</option>
                @endforeach
            </select>
        </div>
    @else
        <input type="hidden" name="semester" value="{{ $semesterBerjalan }}">
        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaranBerjalan }}">
        <div class="col-span-12">
            <div class="alert alert-primary-soft show flex items-center text-sm" role="note">
                <i data-feather="calendar" class="w-4 h-4 mr-2 flex-none"></i>
                Dicatat untuk Semester {{ $semesterBerjalan }}, tahun ajaran {{ $tahunAjaranBerjalan }}/{{ $tahunAjaranBerjalan + 1 }}.
            </div>
        </div>
    @endif

    <div class="col-span-12 text-xs font-medium uppercase tracking-wide text-slate-500 mt-2">Antropometri</div>
    <div class="col-span-6 sm:col-span-4">
        <label for="{{ $prefix }}berat_badan" class="form-label">Berat Badan</label>
        <div class="input-group">
            <input id="{{ $prefix }}berat_badan" name="berat_badan" type="number" inputmode="decimal" min="1" max="200" step="0.1" class="form-control" value="{{ $value('berat_badan') }}" placeholder="30">
            <div class="input-group-text">kg</div>
        </div>
    </div>
    <div class="col-span-6 sm:col-span-4">
        <label for="{{ $prefix }}tinggi_badan" class="form-label">Tinggi Badan</label>
        <div class="input-group">
            <input id="{{ $prefix }}tinggi_badan" name="tinggi_badan" type="number" inputmode="decimal" min="50" max="250" step="0.1" class="form-control" value="{{ $value('tinggi_badan') }}" placeholder="130">
            <div class="input-group-text">cm</div>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-4">
        <div class="form-label">BMI (otomatis)</div>
        <div class="form-control bg-slate-50 dark:bg-darkmode-800 font-medium" data-bmi-preview>-</div>
    </div>

    <div class="col-span-12 text-xs font-medium uppercase tracking-wide text-slate-500 mt-2">Pemeriksaan Fisik</div>
    <div class="col-span-6 sm:col-span-3">
        <label for="{{ $prefix }}penglihatan_kiri" class="form-label">Penglihatan Kiri</label>
        <input id="{{ $prefix }}penglihatan_kiri" name="penglihatan_kiri" type="text" maxlength="10" class="form-control" value="{{ $value('penglihatan_kiri') }}" placeholder="6/6">
    </div>
    <div class="col-span-6 sm:col-span-3">
        <label for="{{ $prefix }}penglihatan_kanan" class="form-label">Penglihatan Kanan</label>
        <input id="{{ $prefix }}penglihatan_kanan" name="penglihatan_kanan" type="text" maxlength="10" class="form-control" value="{{ $value('penglihatan_kanan') }}" placeholder="6/6">
    </div>
    <div class="col-span-6 sm:col-span-3">
        <label for="{{ $prefix }}pendengaran" class="form-label">Pendengaran</label>
        <select id="{{ $prefix }}pendengaran" name="pendengaran" class="form-select">
            <option value="">Pilih kondisi</option>
            @foreach (['normal' => 'Normal', 'kurang' => 'Kurang', 'tuli' => 'Tuli'] as $key => $label)
                <option value="{{ $key }}" @if ($value('pendengaran') === $key) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-span-6 sm:col-span-3">
        <label for="{{ $prefix }}kondisi_gigi" class="form-label">Kondisi Gigi</label>
        <select id="{{ $prefix }}kondisi_gigi" name="kondisi_gigi" class="form-select">
            <option value="">Pilih kondisi</option>
            @foreach (['baik' => 'Baik', 'caries' => 'Caries', 'perlu_perawatan' => 'Perlu Perawatan'] as $key => $label)
                <option value="{{ $key }}" @if ($value('kondisi_gigi') === $key) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-span-12 text-xs font-medium uppercase tracking-wide text-slate-500 mt-2 {{ $isGuru ? '' : 'hidden' }}" data-guru-field>Cek Lab (khusus guru)</div>
    <div class="col-span-6 {{ $isGuru ? '' : 'hidden' }}" data-guru-field>
        <label for="{{ $prefix }}gula_darah" class="form-label">Gula Darah</label>
        <div class="input-group">
            <input id="{{ $prefix }}gula_darah" name="gula_darah" type="number" inputmode="decimal" min="0" max="1000" step="0.1" class="form-control" value="{{ $value('gula_darah') }}" placeholder="120" @unless ($isGuru) disabled @endunless>
            <div class="input-group-text">mg/dL</div>
        </div>
    </div>
    <div class="col-span-6 {{ $isGuru ? '' : 'hidden' }}" data-guru-field>
        <label for="{{ $prefix }}kolesterol" class="form-label">Kolesterol</label>
        <div class="input-group">
            <input id="{{ $prefix }}kolesterol" name="kolesterol" type="number" inputmode="decimal" min="0" max="1000" step="0.1" class="form-control" value="{{ $value('kolesterol') }}" placeholder="180" @unless ($isGuru) disabled @endunless>
            <div class="input-group-text">mg/dL</div>
        </div>
    </div>

    <div class="col-span-12">
        <label for="{{ $prefix }}catatan" class="form-label">Catatan</label>
        <textarea id="{{ $prefix }}catatan" name="catatan" class="form-control" rows="3" placeholder="Catatan untuk orang tua atau tindak lanjut UKS...">{{ $value('catatan') }}</textarea>
    </div>
</div>
