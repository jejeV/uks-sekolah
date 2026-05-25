@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">

                {{-- Alert --}}
                @if (session('success'))
                    <div class="col-span-12 mt-5">
                        <div class="alert alert-success show flex items-center" role="alert">
                            <i data-feather="check-circle" class="w-6 h-6 mr-2"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                {{-- Stat Cards --}}
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Ringkasan UKS</h2>
                        <span class="ml-auto text-slate-500 text-sm">{{ now()->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="users" class="report-box__icon text-primary"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $total_siswa }}</div>
                                    <div class="text-base text-slate-500 mt-1">Total Siswa Aktif</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="user-check" class="report-box__icon text-pending"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $total_guru }}</div>
                                    <div class="text-base text-slate-500 mt-1">Total Guru & Staff</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="calendar" class="report-box__icon text-success"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $kunjungan_bulan }}</div>
                                    <div class="text-base text-slate-500 mt-1">Kunjungan Bulan Ini</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="clipboard" class="report-box__icon text-primary"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $total_pemeriksaan }}</div>
                                    <div class="text-base text-slate-500 mt-1">Total Pemeriksaan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Kunjungan --}}
                <div class="col-span-12 lg:col-span-6 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Status Kunjungan Bulan Ini</h2>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        @php $statusList = ['ringan' => 'success', 'sedang' => 'warning', 'berat' => 'danger', 'dirujuk' => 'pending']; @endphp
                        @forelse ($kunjungan_per_status as $status => $total)
                            <div class="flex items-center {{ !$loop->first ? 'mt-4' : '' }}">
                                <div class="w-2 h-2 bg-{{ $statusList[$status] ?? 'primary' }} rounded-full mr-3"></div>
                                <span class="truncate capitalize">{{ $status }}</span>
                                <span class="font-medium xl:ml-auto">{{ $total }} kunjungan</span>
                            </div>
                        @empty
                            <div class="text-slate-500 text-center py-4">Belum ada kunjungan bulan ini.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Export --}}
                <div class="col-span-12 lg:col-span-6 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Export Data</h2>
                    </div>
                    <div class="intro-y box p-5 mt-5 grid grid-cols-1 gap-3">
                        <a href="{{ route('export.kunjungan') }}" class="btn btn-outline-primary w-full flex items-center justify-center gap-2">
                            <i data-feather="download" class="w-4 h-4"></i> Export Riwayat Kunjungan UKS
                        </a>
                        <a href="{{ route('export.riwayat') }}" class="btn btn-outline-warning w-full flex items-center justify-center gap-2">
                            <i data-feather="download" class="w-4 h-4"></i> Export Riwayat Penyakit
                        </a>
                        <a href="{{ route('export.pemeriksaan') }}" class="btn btn-outline-success w-full flex items-center justify-center gap-2">
                            <i data-feather="download" class="w-4 h-4"></i> Export Raport Kesehatan
                        </a>
                    </div>
                </div>

                {{-- Tabel Kunjungan --}}
                <div class="col-span-12 mt-6">
                    <div class="intro-y flex flex-wrap sm:flex-nowrap items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Kunjungan Terbaru</h2>
                        <button data-tw-toggle="modal" data-tw-target="#modal-tambah"
                            class="btn btn-primary shadow-md ml-auto">
                            <i data-feather="plus" class="w-4 h-4 mr-1"></i> Tambah
                        </button>
                        <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-3">
                            <div class="w-48 relative text-slate-500">
                                <input type="text" id="search-table" class="form-control w-48 box pr-10" placeholder="Cari nama...">
                                <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-feather="search"></i>
                            </div>
                        </div>
                    </div>

                    <div class="intro-y col-span-12 overflow-auto lg:overflow-visible mt-5 sm:mt-2">
                        <table class="table table-report -mt-2" id="tabel-kunjungan">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Nama</th>
                                    <th class="whitespace-nowrap">Jenjang</th>
                                    <th class="whitespace-nowrap">Tanggal</th>
                                    <th class="whitespace-nowrap">Keluhan</th>
                                    <th class="text-center whitespace-nowrap">Status</th>
                                    <th class="text-center whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kunjungan_terbaru as $item)
                                    <tr class="intro-x">
                                        <td>
                                            <div class="font-medium whitespace-nowrap">{{ $item->anggota->nama }}</div>
                                            <div class="text-slate-500 text-xs mt-0.5 capitalize">{{ $item->anggota->tipe }}</div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $item->anggota->jenjang->nama }}</td>
                                        <td class="whitespace-nowrap">{{ $item->tanggal->format('d/m/Y') }}</td>
                                        <td><div class="truncate max-w-xs">{{ $item->keluhan }}</div></td>
                                        <td class="text-center">
                                            @php
                                                $badge = match($item->status) {
                                                    'ringan'  => 'success',
                                                    'sedang'  => 'warning',
                                                    'berat'   => 'danger',
                                                    'dirujuk' => 'pending',
                                                    default   => 'secondary',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $badge }}/20 text-{{ $badge }} capitalize font-medium">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td class="table-report__action w-40">
                                            <div class="flex justify-center items-center">
                                                <a class="flex items-center mr-3 cursor-pointer"
                                                   data-tw-toggle="modal"
                                                   data-tw-target="#modal-edit"
                                                   data-id="{{ $item->id }}"
                                                   data-anggota_id="{{ $item->anggota_id }}"
                                                   data-tanggal="{{ $item->tanggal->format('Y-m-d') }}"
                                                   data-jam="{{ $item->jam }}"
                                                   data-keluhan="{{ $item->keluhan }}"
                                                   data-diagnosis="{{ $item->diagnosis }}"
                                                   data-tindakan="{{ $item->tindakan }}"
                                                   data-obat="{{ $item->obat }}"
                                                   data-status="{{ $item->status }}"
                                                   onclick="bukaEdit(this)">
                                                    <i data-feather="check-square" class="w-4 h-4 mr-1"></i> Edit
                                                </a>
                                                <a class="flex items-center text-danger cursor-pointer"
                                                   data-tw-toggle="modal"
                                                   data-tw-target="#modal-hapus"
                                                   data-id="{{ $item->id }}"
                                                   data-nama="{{ $item->anggota->nama }}"
                                                   onclick="bukaHapus(this)">
                                                    <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-slate-500 py-6">Belum ada data kunjungan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="intro-y flex flex-wrap sm:flex-nowrap items-center mt-3">
                        <div class="hidden md:block mr-auto text-slate-500 text-sm">
                            Menampilkan {{ $kunjungan_terbaru->firstItem() ?? 0 }} -
                            {{ $kunjungan_terbaru->lastItem() ?? 0 }} dari
                            {{ $kunjungan_terbaru->total() }} data
                        </div>
                        {{ $kunjungan_terbaru->links() }}
                    </div>
                </div>

            </div>
        </div>

        {{-- Sidebar Kanan --}}
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-6">

                    {{-- Menu Cepat --}}
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3 2xl:mt-8">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Menu Cepat</h2>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-siswa"
                               class="intro-x box p-4 flex flex-col items-center zoom-in text-center hover:bg-primary hover:text-white transition-colors">
                                <i data-feather="user-plus" class="w-8 h-8 mb-2"></i>
                                <span class="text-sm font-medium">Tambah Siswa</span>
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan"
                               class="intro-x box p-4 flex flex-col items-center zoom-in text-center hover:bg-primary hover:text-white transition-colors">
                                <i data-feather="clipboard" class="w-8 h-8 mb-2"></i>
                                <span class="text-sm font-medium">Tambah Pemeriksaan</span>
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah"
                               class="intro-x box p-4 flex flex-col items-center zoom-in text-center hover:bg-primary hover:text-white transition-colors">
                                <i data-feather="plus-circle" class="w-8 h-8 mb-2"></i>
                                <span class="text-sm font-medium">Tambah Kunjungan</span>
                            </button>
                            <a href="{{ route('anggota.index') }}"
                               class="intro-x box p-4 flex flex-col items-center zoom-in text-center hover:bg-primary hover:text-white transition-colors">
                                <i data-feather="users" class="w-8 h-8 mb-2"></i>
                                <span class="text-sm font-medium">Data Anggota</span>
                            </a>
                        </div>
                    </div>

                    {{-- Info Petugas --}}
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Info Petugas</h2>
                        </div>
                        <div class="intro-x box p-5 mt-5">
                            <div class="flex items-center">
                                <div class="w-12 h-12 flex-none rounded-full bg-primary/20 flex items-center justify-center">
                                    <i data-feather="user" class="w-6 h-6 text-primary"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium">{{ Auth::user()->name }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5 capitalize">{{ Auth::user()->role }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ Auth::user()->email }}</div>
                                </div>
                            </div>
                            <div class="border-t border-slate-200/60 mt-4 pt-4">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-full flex items-center justify-center gap-2">
                                        <i data-feather="log-out" class="w-4 h-4"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL TAMBAH --}}
    {{-- ============================================ --}}
    <div id="modal-tambah" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('dashboard.kunjungan.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Tambah Kunjungan UKS</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Nama Anggota <span class="text-danger">*</span></label>
                            <select name="anggota_id" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach (App\Models\Anggota::with('jenjang')->where('aktif', true)->orderBy('nama')->get() as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ ucfirst($a->tipe) }} - {{ $a->jenjang->nama }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" class="form-control" value="{{ date('H:i') }}">
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                            <textarea name="keluhan" class="form-control" rows="2" placeholder="Keluhan pasien..." required></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="2" placeholder="Diagnosis..."></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Tindakan</label>
                            <textarea name="tindakan" class="form-control" rows="2" placeholder="Tindakan yang diberikan..."></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Obat</label>
                            <input type="text" name="obat" class="form-control" placeholder="Nama obat...">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="ringan">Ringan</option>
                                <option value="sedang">Sedang</option>
                                <option value="berat">Berat</option>
                                <option value="dirujuk">Dirujuk</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                        <button type="submit" class="btn btn-primary w-24">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL TAMBAH SISWA --}}
    <div id="modal-tambah-siswa" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('anggota.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Tambah Siswa</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="jenjang_id" class="form-label">Jenjang <span class="text-danger">*</span></label>
                            <select id="jenjang_id" name="jenjang_id" class="form-select" required>
                                <option value="">-- Pilih Jenjang --</option>
                                @foreach ($jenjang as $item)
                                    <option value="{{ $item->id }}" {{ old('jenjang_id') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select id="tipe" name="tipe" class="form-select" required>
                                <option value="siswa" {{ old('tipe') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="guru" {{ old('tipe') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="tenaga_kependidikan" {{ old('tipe') == 'tenaga_kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="nis_nip" class="form-label">NIS / NIP <span class="text-danger">*</span></label>
                            <input id="nis_nip" name="nis_nip" type="text" class="form-control" value="{{ old('nis_nip') }}" placeholder="Masukkan NIS / NIP" required>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input id="nama" name="nama" type="text" class="form-control" value="{{ old('nama') }}" placeholder="Masukkan nama siswa" required>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="kelas" class="form-label">Kelas</label>
                            <input id="kelas" name="kelas" type="text" class="form-control" value="{{ old('kelas') }}" placeholder="Masukkan kelas">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input id="tgl_lahir" name="tgl_lahir" type="date" class="form-control" value="{{ old('tgl_lahir') }}">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <label class="form-check">
                                    <input id="jenis_kelamin_L" type="radio" class="form-check-input" name="jenis_kelamin" value="L" {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }}>
                                    <span class="form-check-label">Laki-laki</span>
                                </label>
                                <label class="form-check">
                                    <input id="jenis_kelamin_P" type="radio" class="form-check-input" name="jenis_kelamin" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                    <span class="form-check-label">Perempuan</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                        <button type="submit" class="btn btn-primary w-24">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL TAMBAH PEMERIKSAAN --}}
    {{-- ============================================ --}}
    <div id="modal-tambah-pemeriksaan" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('pemeriksaan.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Tambah Pemeriksaan</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="tambah-anggota_id" class="form-label">Anggota <span class="text-danger">*</span></label>
                            <select id="tambah-anggota_id" name="anggota_id" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach ($pemeriksaan_anggota as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ ucfirst($a->tipe) }} - {{ $a->jenjang->nama }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label for="tambah-semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select id="tambah-semester" name="semester" class="form-select" required>
                                <option value="">Pilih semester</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label for="tambah-tahun_ajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select id="tambah-tahun_ajaran" name="tahun_ajaran" class="form-select" required>
                                <option value="">Pilih tahun</option>
                                @foreach (range(now()->year, now()->year - 5) as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="tambah-berat_badan" class="form-label">Berat Badan (kg)</label>
                            <input id="tambah-berat_badan" name="berat_badan" type="number" class="form-control" min="1" max="200" step="0.1" placeholder="70">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="tambah-tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                            <input id="tambah-tinggi_badan" name="tinggi_badan" type="number" class="form-control" min="50" max="250" step="0.1" placeholder="160">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="tambah-penglihatan_kiri" class="form-label">Penglihatan Kiri</label>
                            <input id="tambah-penglihatan_kiri" name="penglihatan_kiri" type="text" class="form-control" placeholder="1.0">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="tambah-penglihatan_kanan" class="form-label">Penglihatan Kanan</label>
                            <input id="tambah-penglihatan_kanan" name="penglihatan_kanan" type="text" class="form-control" placeholder="1.0">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="tambah-pendengaran" class="form-label">Pendengaran</label>
                            <select id="tambah-pendengaran" name="pendengaran" class="form-select">
                                <option value="">Pilih kondisi</option>
                                <option value="normal">Normal</option>
                                <option value="kurang">Kurang</option>
                                <option value="tuli">Tuli</option>
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="tambah-kondisi_gigi" class="form-label">Kondisi Gigi</label>
                            <select id="tambah-kondisi_gigi" name="kondisi_gigi" class="form-select">
                                <option value="">Pilih kondisi</option>
                                <option value="baik">Baik</option>
                                <option value="caries">Caries</option>
                                <option value="perlu_perawatan">Perlu Perawatan</option>
                            </select>
                        </div>
                        <div class="col-span-12">
                            <label for="tambah-catatan" class="form-label">Catatan</label>
                            <textarea id="tambah-catatan" name="catatan" class="form-control" rows="3" placeholder="Catatan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                        <button type="submit" class="btn btn-primary w-24">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL EDIT --}}
    {{-- ============================================ --}}
    <div id="modal-edit" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="form-edit" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Kunjungan UKS</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Nama Anggota <span class="text-danger">*</span></label>
                            <select name="anggota_id" id="edit-anggota_id" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach (App\Models\Anggota::with('jenjang')->where('aktif', true)->orderBy('nama')->get() as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ ucfirst($a->tipe) }} - {{ $a->jenjang->nama }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="edit-tanggal" class="form-control" required>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" id="edit-jam" class="form-control">
                        </div>
                        <div class="col-span-12">
                            <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                            <textarea name="keluhan" id="edit-keluhan" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Diagnosis</label>
                            <textarea name="diagnosis" id="edit-diagnosis" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Tindakan</label>
                            <textarea name="tindakan" id="edit-tindakan" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Obat</label>
                            <input type="text" name="obat" id="edit-obat" class="form-control">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="edit-status" class="form-select" required>
                                <option value="ringan">Ringan</option>
                                <option value="sedang">Sedang</option>
                                <option value="berat">Berat</option>
                                <option value="dirujuk">Dirujuk</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                        <button type="submit" class="btn btn-primary w-24">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL HAPUS --}}
    {{-- ============================================ --}}
    <div id="modal-hapus" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="form-hapus" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body p-0">
                        <div class="p-5 text-center">
                            <i data-feather="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                            <div class="text-3xl mt-5">Yakin hapus?</div>
                            <div class="text-slate-500 mt-2">
                                Data kunjungan <strong id="hapus-nama"></strong> akan dihapus permanen.
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                            <button type="submit" class="btn btn-danger w-24">Hapus</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
    // Isi form edit dari data-attribute
    function bukaEdit(el) {
        const d = el.dataset
        document.getElementById('form-edit').action = '{{ url('kunjungan-dashboard') }}/' + d.id
        document.getElementById('edit-anggota_id').value = d.anggota_id || ''
        document.getElementById('edit-tanggal').value    = d.tanggal || ''
        document.getElementById('edit-jam').value        = d.jam || ''
        document.getElementById('edit-keluhan').value    = d.keluhan || ''
        document.getElementById('edit-diagnosis').value  = d.diagnosis || ''
        document.getElementById('edit-tindakan').value   = d.tindakan || ''
        document.getElementById('edit-obat').value       = d.obat || ''
        document.getElementById('edit-status').value     = d.status || ''
    }

    // Set action form hapus
    function bukaHapus(el) {
        document.getElementById('form-hapus').action    = '{{ url('kunjungan-dashboard') }}/' + el.dataset.id
        document.getElementById('hapus-nama').textContent = el.dataset.nama || ''
    }

    // Search real-time
    const searchTable = document.getElementById('search-table')
    if (searchTable) {
        searchTable.addEventListener('keyup', function () {
            const keyword = this.value.toLowerCase()
            document.querySelectorAll('#tabel-kunjungan tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(keyword) ? '' : 'none'
            })
        })
    }
</script>
@endsection
