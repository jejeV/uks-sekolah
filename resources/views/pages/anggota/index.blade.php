@extends('../layout/' . $layout)

@section('subhead')
    <title>Siswa & Guru - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $tipeAktif = request('tipe');
        $chipQuery = fn ($tipe) => array_filter(array_merge(request()->except(['page']), ['tipe' => $tipe]));
        $hasFilter = request()->hasAny(['search', 'tipe', 'jenjang_id', 'kelas']);
        $tipeBadge = [
            'siswa'               => 'bg-primary/10 text-primary',
            'guru'                => 'bg-success/10 text-success',
            'tenaga_kependidikan' => 'bg-slate-200 text-slate-600 dark:bg-darkmode-400 dark:text-slate-300',
        ];
    @endphp

    <div class="intro-y flex flex-col sm:flex-row sm:items-center gap-3 mt-8">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">Siswa & Guru</h2>
            <div class="text-slate-500 text-sm mt-1">
                {{ $ringkasan['siswa'] }} siswa · {{ $ringkasan['guru'] }} guru · {{ $ringkasan['kelas'] }} kelas
                <span class="hidden sm:inline">· {{ $ringkasan['laki_laki'] }} laki-laki, {{ $ringkasan['perempuan'] }} perempuan</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="dropdown">
                <button class="dropdown-toggle btn btn-outline-secondary h-10 px-4 gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-feather="upload" class="w-4 h-4"></i>
                    Import CSV
                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                </button>
                <div class="dropdown-menu w-80">
                    <div class="dropdown-content p-4">
                        <form action="{{ route('anggota.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <ol class="text-sm text-slate-600 dark:text-slate-400 list-decimal pl-4 space-y-1">
                                <li><a href="{{ route('anggota.import-template') }}" data-download class="text-primary underline">Unduh template CSV</a></li>
                                <li>Isi data siswa/guru (bisa dari Excel, simpan sebagai CSV).</li>
                                <li>Pilih file lalu klik Import.</li>
                            </ol>
                            <input id="file" name="file" type="file" class="form-control mt-3" accept=".csv,text/csv" required aria-label="File CSV">
                            <div class="text-slate-500 text-xs mt-2">Data dengan nama/NIS yang sama akan diperbarui, bukan diduplikasi.</div>
                            <button type="submit" class="btn btn-primary w-full mt-4 gap-2">
                                <i data-feather="upload-cloud" class="w-4 h-4"></i>
                                Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <a href="{{ route('anggota.create') }}" class="btn btn-primary h-10 px-4 gap-2 shadow-md">
                <i data-feather="plus" class="w-4 h-4"></i>
                Tambah
            </a>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <form method="GET" action="{{ route('anggota.index') }}" class="grid grid-cols-12 gap-3" role="search">
            <input type="hidden" name="tipe" value="{{ $tipeAktif }}">
            <div class="col-span-12 lg:col-span-6">
                <label for="search" class="sr-only">Cari nama atau NIS/NIP</label>
                <div class="relative">
                    <i data-feather="search" class="w-4 h-4 absolute inset-y-0 my-auto left-3 text-slate-400"></i>
                    <input id="search" name="search" value="{{ request('search') }}" type="search" class="form-control pl-9" placeholder="Cari nama atau NIS/NIP, tekan Enter">
                </div>
            </div>
            <div class="col-span-6 lg:col-span-3">
                <label for="jenjang_id" class="sr-only">Jenjang</label>
                <select id="jenjang_id" name="jenjang_id" class="form-select" data-auto-submit>
                    <option value="">Semua jenjang</option>
                    @foreach ($jenjang as $item)
                        <option value="{{ $item->id }}" @if (request('jenjang_id') == $item->id) selected @endif>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 lg:col-span-3">
                <label for="kelas" class="sr-only">Kelas</label>
                <select id="kelas" name="kelas" class="form-select" data-auto-submit>
                    <option value="">Semua kelas</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas }}" @if (request('kelas') === $kelas) selected @endif>Kelas {{ $kelas }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex flex-wrap items-center gap-2 mt-4">
            @foreach ([null => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru', 'tenaga_kependidikan' => 'Tenaga Kependidikan'] as $key => $label)
                <a href="{{ route('anggota.index', $chipQuery($key ?: null)) }}" class="filter-chip {{ $tipeAktif == $key ? 'filter-chip--active' : '' }}">{{ $label }}</a>
            @endforeach
            @if ($hasFilter)
                <a href="{{ route('anggota.index') }}" class="ml-auto text-sm text-primary flex items-center">
                    <i data-feather="x" class="w-4 h-4 mr-1"></i> Reset filter
                </a>
            @endif
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Nama</th>
                        <th class="whitespace-nowrap">Tipe</th>
                        <th class="whitespace-nowrap">Jenjang / Kelas</th>
                        <th class="whitespace-nowrap hidden lg:table-cell">Jenis Kelamin</th>
                        <th class="whitespace-nowrap hidden lg:table-cell">Usia</th>
                        <th class="whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($anggota as $item)
                        <tr class="intro-x">
                            <td>
                                <a href="{{ route('anggota.show', $item) }}" class="font-medium text-primary whitespace-nowrap">{{ $item->nama }}</a>
                                <div class="text-slate-500 text-xs mt-0.5">
                                    {{ $item->nis_nip }}
                                    @unless ($item->aktif)
                                        <span class="badge-status bg-slate-200 text-slate-600 dark:bg-darkmode-400 ml-1 py-0">Nonaktif</span>
                                    @endunless
                                </div>
                            </td>
                            <td><span class="badge-status {{ $tipeBadge[$item->tipe] ?? '' }}">{{ $item->tipe_label }}</span></td>
                            <td class="whitespace-nowrap">{{ $item->jenjang?->nama ?? '-' }}{{ $item->kelas ? ' / ' . $item->kelas : '' }}</td>
                            <td class="whitespace-nowrap hidden lg:table-cell">{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td>
                            <td class="whitespace-nowrap hidden lg:table-cell">{{ $item->tgl_lahir ? $item->tgl_lahir->age . ' th' : '-' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('anggota.show', $item) }}" class="btn btn-sm btn-outline-secondary">Profil</a>
                                    <a href="{{ route('anggota.edit', $item) }}" class="btn btn-sm btn-outline-primary tooltip" title="Ubah" aria-label="Ubah {{ $item->nama }}">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('anggota.destroy', $item) }}" method="POST"
                                          data-confirm="Hapus {{ $item->nama }}? Semua riwayat kunjungan dan raport kesehatannya juga akan terhapus.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger tooltip" title="Hapus" aria-label="Hapus {{ $item->nama }}">
                                            <i data-feather="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-slate-500 py-10">
                                <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-400"></i>
                                @if ($hasFilter)
                                    Tidak ada siswa/guru yang cocok dengan filter.
                                    <a href="{{ route('anggota.index') }}" class="text-primary">Reset filter</a>
                                @else
                                    Belum ada data. <a href="{{ route('anggota.create') }}" class="text-primary">Tambah satu per satu</a> atau gunakan Import CSV.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5">
            <div class="text-slate-500 text-sm">Menampilkan {{ $anggota->firstItem() ?: 0 }}–{{ $anggota->lastItem() ?: 0 }} dari {{ $anggota->total() }} data</div>
            <div>{{ $anggota->links() }}</div>
        </div>
    </div>
@endsection
