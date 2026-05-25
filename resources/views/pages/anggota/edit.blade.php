@extends('../layout/' . $layout)

@section('subhead')
    <title>Edit Anggota - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Edit Anggota</h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="intro-y box p-5">
                @if ($errors->any())
                    <div class="alert alert-danger show mb-5">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('anggota.update', $anggota) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="jenjang_id" class="form-label">Jenjang</label>
                        <select id="jenjang_id" name="jenjang_id" class="form-select">
                            <option value="">Pilih jenjang</option>
                            @foreach ($jenjang as $item)
                                <option value="{{ $item->id }}" {{ old('jenjang_id', $anggota->jenjang_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3">
                        <label for="nis_nip" class="form-label">NIS / NIP</label>
                        <input id="nis_nip" name="nis_nip" type="text" class="form-control" value="{{ old('nis_nip', $anggota->nis_nip) }}" placeholder="Masukkan NIS atau NIP">
                    </div>

                    <div class="mt-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input id="nama" name="nama" type="text" class="form-control" value="{{ old('nama', $anggota->nama) }}" placeholder="Masukkan nama siswa">
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <label class="form-check">
                                <input type="radio" class="form-check-input" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $anggota->jenis_kelamin) === 'L' ? 'checked' : '' }}>
                                <span class="form-check-label">Laki-laki</span>
                            </label>
                            <label class="form-check">
                                <input type="radio" class="form-check-input" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $anggota->jenis_kelamin) === 'P' ? 'checked' : '' }}>
                                <span class="form-check-label">Perempuan</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="tipe" class="form-label">Tipe</label>
                        <select id="tipe" name="tipe" class="form-select">
                            <option value="siswa" {{ old('tipe', $anggota->tipe) === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ old('tipe', $anggota->tipe) === 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="tenaga_kependidikan" {{ old('tipe', $anggota->tipe) === 'tenaga_kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <label for="kelas" class="form-label">Kelas</label>
                        <input id="kelas" name="kelas" type="text" class="form-control" value="{{ old('kelas', $anggota->kelas) }}" placeholder="Masukkan kelas (opsional)">
                    </div>

                    <div class="mt-3">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input id="tgl_lahir" name="tgl_lahir" type="date" class="form-control" value="{{ old('tgl_lahir', optional($anggota->tgl_lahir)->format('Y-m-d')) }}">
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
