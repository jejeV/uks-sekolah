{{-- Form anggota (tambah & ubah). Parameter: $anggota (opsional), $jenjang --}}
@php
    $a = $anggota ?? null;
    $value = fn ($key) => old($key, optional($a)->$key);
    $tglLahir = old('tgl_lahir', optional(optional($a)->tgl_lahir)->toDateString());
    $tipe = $value('tipe') ?? 'siswa';
@endphp

<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-6">
        <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
        <select id="tipe" name="tipe" class="form-select" required>
            @foreach (\App\Models\Anggota::TIPE_LABEL as $key => $label)
                <option value="{{ $key }}" @if ($tipe === $key) selected @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-span-12 sm:col-span-6">
        <label for="jenjang_id" class="form-label">Jenjang <span class="text-danger">*</span></label>
        <select id="jenjang_id" name="jenjang_id" class="form-select" required>
            <option value="">Pilih jenjang</option>
            @foreach ($jenjang as $item)
                <option value="{{ $item->id }}" @if ($value('jenjang_id') == $item->id) selected @endif>{{ $item->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <label for="nis_nip" class="form-label">NIS / NIP <span class="text-danger">*</span></label>
        <input id="nis_nip" name="nis_nip" type="text" class="form-control" value="{{ $value('nis_nip') }}" placeholder="Nomor induk siswa / pegawai" required>
    </div>
    <div class="col-span-12 sm:col-span-6">
        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input id="nama" name="nama" type="text" maxlength="100" class="form-control" value="{{ $value('nama') }}" placeholder="Nama sesuai data sekolah" required>
    </div>

    <div class="col-span-12 sm:col-span-6">
        <span class="form-label block">Jenis Kelamin <span class="text-danger">*</span></span>
        <div class="flex gap-4 mt-2">
            <label class="form-check">
                <input type="radio" class="form-check-input" name="jenis_kelamin" value="L" @if ($value('jenis_kelamin') === 'L') checked @endif required>
                <span class="form-check-label">Laki-laki</span>
            </label>
            <label class="form-check">
                <input type="radio" class="form-check-input" name="jenis_kelamin" value="P" @if ($value('jenis_kelamin') === 'P') checked @endif>
                <span class="form-check-label">Perempuan</span>
            </label>
        </div>
    </div>
    <div class="col-span-12 sm:col-span-6">
        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
        <input id="tgl_lahir" name="tgl_lahir" type="date" class="form-control" value="{{ $tglLahir }}" max="{{ now()->toDateString() }}">
    </div>

    <div class="col-span-12 sm:col-span-6">
        <label for="kelas" class="form-label">Kelas</label>
        <input id="kelas" name="kelas" type="text" maxlength="20" class="form-control" value="{{ $value('kelas') }}" placeholder="Contoh: 2A">
        <div class="form-help">Wajib untuk siswa; kosongkan untuk guru dan tenaga kependidikan.</div>
    </div>

    @if ($a)
        <div class="col-span-12 sm:col-span-6">
            <span class="form-label block">Status</span>
            <input type="hidden" name="aktif" value="0">
            <label class="form-check form-switch mt-2">
                <input type="checkbox" class="form-check-input" name="aktif" value="1" @if (old('aktif', $a->aktif)) checked @endif>
                <span class="form-check-label">Aktif (tampil di daftar pemeriksaan & pencarian)</span>
            </label>
        </div>
    @endif
</div>
