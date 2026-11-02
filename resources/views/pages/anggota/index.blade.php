@extends('../layout/' . $layout)

@section('subhead')
    <title>Data Anggota - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $tipeAktif = request('tipe');
        $tabQuery = fn ($tipe) => array_merge(request()->except(['page']), ['tipe' => $tipe]);
        $resetTabQuery = request()->except(['tipe', 'page']);
        $tabClass = fn ($tipe) => $tipeAktif === $tipe ? 'border-primary bg-primary/5 shadow-md' : 'border-transparent';
    @endphp

    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Data Anggota</h2>
        <div class="flex flex-wrap items-center gap-2 mt-3 sm:mt-0">
            <div class="dropdown">
                <button class="dropdown-toggle btn btn-outline-secondary h-10 px-4 gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-feather="upload" class="w-4 h-4"></i>
                    Import
                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                </button>
                <div class="dropdown-menu w-80">
                    <div class="dropdown-content p-4">
                        <form action="{{ route('anggota.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <label for="file" class="form-label">Import Data Anggota</label>
                            <input id="file" name="file" type="file" class="form-control" accept=".csv,text/csv">
                            <div class="text-slate-500 text-xs mt-2 leading-relaxed">
                                Gunakan CSV berisi nis_nip, nama, jenjang, tipe, kelas, tgl_lahir, jenis_kelamin.
                            </div>
                            <div class="flex items-center justify-between gap-3 mt-4">
                                <a href="{{ route('anggota.import-template') }}" class="text-primary text-sm">
                                    Unduh template
                                </a>
                                <button type="submit" class="btn btn-primary h-10 px-4 gap-2">
                                    <i data-feather="upload-cloud" class="w-4 h-4"></i>
                                    Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <a href="{{ route('anggota.create') }}" class="btn btn-primary h-10 px-4 gap-2">
                <i data-feather="plus" class="w-4 h-4"></i>
                Tambah Anggota
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <div class="grid gap-4 mb-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="intro-y">
                    <a href="{{ route('anggota.index', $tabQuery('siswa')) }}" class="box p-5 block border {{ $tabClass('siswa') }}">
                        <div class="flex items-center">
                            <div class="w-11 h-11 rounded-full bg-primary/10 flex items-center justify-center">
                                <i data-feather="users" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-slate-500 text-sm">Total Siswa</div>
                                <div class="text-2xl font-medium mt-1">{{ $ringkasan['siswa'] }}</div>
                                @if ($tipeAktif === 'siswa')
                                    <div class="text-primary text-xs mt-1">Tab aktif</div>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                <div class="intro-y">
                    <a href="{{ route('anggota.index', $tabQuery('guru')) }}" class="box p-5 block border {{ $tabClass('guru') }}">
                        <div class="flex items-center">
                            <div class="w-11 h-11 rounded-full bg-success/10 flex items-center justify-center">
                                <i data-feather="user-check" class="w-5 h-5 text-success"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-slate-500 text-sm">Total Guru</div>
                                <div class="text-2xl font-medium mt-1">{{ $ringkasan['guru'] }}</div>
                                @if ($tipeAktif === 'guru')
                                    <div class="text-primary text-xs mt-1">Tab aktif</div>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                <div class="intro-y">
                    <div class="box p-5">
                        <div class="flex items-center">
                            <div class="w-11 h-11 rounded-full bg-warning/10 flex items-center justify-center">
                                <i data-feather="book-open" class="w-5 h-5 text-warning"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-slate-500 text-sm">Total Kelas</div>
                                <div class="text-2xl font-medium mt-1">{{ $ringkasan['kelas'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y">
                    <div class="box p-5">
                        <div class="flex items-center">
                            <div class="w-11 h-11 rounded-full bg-pending/10 flex items-center justify-center">
                                <i data-feather="user" class="w-5 h-5 text-pending"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-slate-500 text-sm">Laki-laki</div>
                                <div class="text-2xl font-medium mt-1">{{ $ringkasan['laki_laki'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y">
                    <div class="box p-5">
                        <div class="flex items-center">
                            <div class="w-11 h-11 rounded-full bg-danger/10 flex items-center justify-center">
                                <i data-feather="user" class="w-5 h-5 text-danger"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-slate-500 text-sm">Perempuan</div>
                                <div class="text-2xl font-medium mt-1">{{ $ringkasan['perempuan'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (in_array($tipeAktif, ['siswa', 'guru'], true))
                <div class="alert alert-primary-soft show flex flex-col sm:flex-row sm:items-center gap-3 mb-5" role="alert">
                    <div class="flex items-center mr-auto">
                        <i data-feather="info" class="w-5 h-5 mr-2"></i>
                        <span>Tab aktif: {{ ucfirst($tipeAktif) }}. Tabel sedang menampilkan data {{ $tipeAktif }}.</span>
                    </div>
                    <a href="{{ route('anggota.index', $resetTabQuery) }}" class="btn btn-outline-primary h-9 px-3">Reset Tab</a>
                </div>
            @endif

            <div class="box p-5">
                <form method="GET" action="{{ route('anggota.index') }}" class="grid grid-cols-12 gap-4 mb-5" role="search">
                    <div class="col-span-12 xl:col-span-4">
                        <label for="search" class="form-label">Cari</label>
                        <div class="relative">
                            <i data-feather="search" class="w-4 h-4 absolute inset-y-0 my-auto left-3 text-slate-400"></i>
                            <input id="search" name="search" value="{{ request('search') }}" type="search" class="form-control pl-9" placeholder="Cari nama atau NIS/NIP, tekan Enter">
                        </div>
                    </div>
                    <div class="col-span-6 xl:col-span-3">
                        <label for="tipe" class="form-label">Tipe</label>
                        <select id="tipe" name="tipe" class="form-select" data-auto-submit>
                            <option value="">Semua</option>
                            <option value="siswa" {{ request('tipe') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('tipe') === 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="tenaga_kependidikan" {{ request('tipe') === 'tenaga_kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-2">
                        <label for="jenjang_id" class="form-label">Jenjang</label>
                        <select id="jenjang_id" name="jenjang_id" class="form-select" data-auto-submit>
                            <option value="">Semua</option>
                            @foreach ($jenjang as $item)
                                <option value="{{ $item->id }}" {{ request('jenjang_id') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-2">
                        <label for="kelas" class="form-label">Kelas</label>
                        <select id="kelas" name="kelas" class="form-select" data-auto-submit>
                            <option value="">Semua</option>
                            @foreach ($kelasOptions as $kelas)
                                <option value="{{ $kelas }}" {{ request('kelas') === $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-1 flex items-end">
                        @if (request()->hasAny(['search', 'tipe', 'jenjang_id', 'kelas']))
                            <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary w-full h-10 tooltip" title="Reset filter" aria-label="Reset filter">
                                <i data-feather="x" class="w-4 h-4"></i>
                            </a>
                        @else
                            <button type="submit" class="btn btn-primary w-full h-10 tooltip" title="Cari data" aria-label="Cari data">
                                <i data-feather="search" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="table table-report -mt-2">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">NIS / NIP</th>
                                <th class="whitespace-nowrap">Nama</th>
                                <th class="whitespace-nowrap">Jenjang</th>
                                <th class="whitespace-nowrap">Tipe</th>
                                <th class="whitespace-nowrap">Kelas</th>
                                <th class="whitespace-nowrap">Tanggal Lahir</th>
                                <th class="whitespace-nowrap">JK</th>
                                <th class="text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($anggota as $item)
                                <tr class="intro-x">
                                    <td>{{ $item->nis_nip }}</td>
                                    <td>
                                        <a href="{{ route('anggota.show', $item) }}" class="font-medium text-primary">{{ $item->nama }}</a>
                                    </td>
                                    <td>{{ $item->jenjang?->nama ?? '-' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $item->tipe)) }}</td>
                                    <td>{{ $item->kelas ?: '-' }}</td>
                                    <td>{{ optional($item->tgl_lahir)->format('d-m-Y') ?: '-' }}</td>
                                    <td>{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td>
                                    <td class="table-report__action w-48">
                                        <div class="flex justify-center items-center gap-2">
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
                                    <td colspan="8" class="text-center py-10">Belum ada data anggota.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-5">
                    <div class="text-slate-500">Menampilkan {{ $anggota->firstItem() ?: 0 }} sampai {{ $anggota->lastItem() ?: 0 }} dari {{ $anggota->total() }} anggota</div>
                    <div>{{ $anggota->withQueryString()->links('pagination::tailwind') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
