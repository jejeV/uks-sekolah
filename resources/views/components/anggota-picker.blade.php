{{--
    Kolom pencarian anggota dengan saran otomatis (lihat resources/js/uks.js).
    Parameter: $pickerId, $tipe (siswa|guru|pemeriksaan|null), $selected (Anggota|null),
               $useOld (bool), $label, $readonly (bool)
--}}
@php
    $selected = $selected ?? null;
    $useOld = $useOld ?? true;
    $readonly = $readonly ?? false;
    $selectedId = $useOld ? old('anggota_id', optional($selected)->id) : optional($selected)->id;
    $selectedLabel = $useOld ? old('anggota_label', optional($selected)->picker_label) : optional($selected)->picker_label;
    $selectedTipe = $useOld ? old('anggota_tipe', optional($selected)->tipe) : optional($selected)->tipe;
@endphp

<div data-anggota-picker data-url="{{ route('anggota.options') }}" data-tipe="{{ $tipe ?? '' }}">
    <label for="{{ $pickerId }}" class="form-label">{{ $label ?? 'Siswa / Guru' }} <span class="text-danger">*</span></label>
    <input type="hidden" name="anggota_id" value="{{ $selectedId }}" data-anggota-id data-tipe="{{ $selectedTipe }}">
    <input type="hidden" name="anggota_tipe" value="{{ $selectedTipe }}" data-anggota-tipe-input>
    <div class="relative">
        <i data-feather="search" class="w-4 h-4 absolute inset-y-0 my-auto left-3 text-slate-400"></i>
        <input id="{{ $pickerId }}" type="text" name="anggota_label" class="form-control pl-9 {{ $selectedId ? 'border-success' : '' }}"
               list="{{ $pickerId }}-list" value="{{ $selectedLabel }}" data-anggota-search
               placeholder="Ketik nama atau NIS/NIP, lalu pilih dari daftar" autocomplete="off" required
               @if ($readonly) readonly @endif>
    </div>
    <datalist id="{{ $pickerId }}-list"></datalist>
    <div class="form-help">Ketik nama atau NIS/NIP, lalu pilih dari daftar saran. Kolom berwarna hijau jika sudah terpilih.</div>
</div>
