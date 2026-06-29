@extends('../layout/' . $layout)

@section('subhead')
    <title>Data Pemeriksaan - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $widgetQuery = fn ($status, $tipe) => array_merge(request()->except(['page']), [
            'status_pemeriksaan' => $status,
            'tipe_anggota' => $tipe,
        ]);
        $resetWidgetQuery = request()->except(['status_pemeriksaan', 'tipe_anggota', 'page']);
        $widgetClass = fn ($status, $tipe) => $widgetStatus === $status && $widgetTipe === $tipe
            ? 'border-primary bg-primary/5 shadow-md'
            : 'border-transparent';
    @endphp

    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Data Pemeriksaan Kesehatan</h2>
        <div class="flex flex-wrap items-center gap-2 mt-3 sm:mt-0">
            <div class="dropdown">
                <button class="dropdown-toggle btn btn-outline-secondary h-10 px-4 gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-feather="download" class="w-4 h-4"></i>
                    Export
                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                </button>
                <div class="dropdown-menu w-48">
                    <ul class="dropdown-content">
                        <li><a href="{{ route('export.pemeriksaan', array_merge(request()->query(), ['format' => 'excel'])) }}" class="dropdown-item"><i data-feather="file-text" class="w-4 h-4 mr-2"></i>Export Excel</a></li>
                        <li><a href="{{ route('export.pemeriksaan', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="dropdown-item"><i data-feather="file" class="w-4 h-4 mr-2"></i>Export PDF</a></li>
                    </ul>
                </div>
            </div>
            <button data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan" class="btn btn-primary h-10 px-4">Tambah Pemeriksaan</button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            @if (session('success'))
                <div class="alert alert-success show flex items-center mb-5" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid gap-4 mb-5" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-11 h-11 rounded-full bg-primary/10 flex items-center justify-center">
                            <i data-feather="clipboard" class="w-5 h-5 text-primary"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-slate-500 text-sm">Total Pemeriksaan</div>
                            <div class="text-2xl font-medium mt-1">{{ $ringkasan['total'] }}</div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('pemeriksaan.index', $widgetQuery('sudah', 'siswa')) }}" class="intro-y box p-5 block border {{ $widgetClass('sudah', 'siswa') }}">
                    <div class="flex items-center">
                        <div class="w-11 h-11 rounded-full bg-success/10 flex items-center justify-center">
                            <i data-feather="users" class="w-5 h-5 text-success"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-slate-500 text-sm">Siswa Diperiksa</div>
                            <div class="text-2xl font-medium mt-1">{{ $ringkasan['siswa_diperiksa'] }}</div>
                            @if ($widgetStatus === 'sudah' && $widgetTipe === 'siswa')
                                <div class="text-primary text-xs mt-1">Filter aktif</div>
                            @endif
                        </div>
                    </div>
                </a>
                <a href="{{ route('pemeriksaan.index', $widgetQuery('sudah', 'guru')) }}" class="intro-y box p-5 block border {{ $widgetClass('sudah', 'guru') }}">
                    <div class="flex items-center">
                        <div class="w-11 h-11 rounded-full bg-success/10 flex items-center justify-center">
                            <i data-feather="user-check" class="w-5 h-5 text-success"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-slate-500 text-sm">Guru Diperiksa</div>
                            <div class="text-2xl font-medium mt-1">{{ $ringkasan['guru_diperiksa'] }}</div>
                            @if ($widgetStatus === 'sudah' && $widgetTipe === 'guru')
                                <div class="text-primary text-xs mt-1">Filter aktif</div>
                            @endif
                        </div>
                    </div>
                </a>
                <a href="{{ route('pemeriksaan.index', $widgetQuery('belum', 'siswa')) }}" class="intro-y box p-5 block border {{ $widgetClass('belum', 'siswa') }}">
                    <div class="flex items-center">
                        <div class="w-11 h-11 rounded-full bg-warning/10 flex items-center justify-center">
                            <i data-feather="user-x" class="w-5 h-5 text-warning"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-slate-500 text-sm">Siswa Belum Diperiksa</div>
                            <div class="text-2xl font-medium mt-1">{{ $ringkasan['siswa_belum_diperiksa'] }}</div>
                            @if ($widgetStatus === 'belum' && $widgetTipe === 'siswa')
                                <div class="text-primary text-xs mt-1">Filter aktif</div>
                            @endif
                        </div>
                    </div>
                </a>
                <a href="{{ route('pemeriksaan.index', $widgetQuery('belum', 'guru')) }}" class="intro-y box p-5 block border {{ $widgetClass('belum', 'guru') }}">
                    <div class="flex items-center">
                        <div class="w-11 h-11 rounded-full bg-warning/10 flex items-center justify-center">
                            <i data-feather="user-minus" class="w-5 h-5 text-warning"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-slate-500 text-sm">Guru Belum Diperiksa</div>
                            <div class="text-2xl font-medium mt-1">{{ $ringkasan['guru_belum_diperiksa'] }}</div>
                            @if ($widgetStatus === 'belum' && $widgetTipe === 'guru')
                                <div class="text-primary text-xs mt-1">Filter aktif</div>
                            @endif
                        </div>
                    </div>
                </a>
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-11 h-11 rounded-full bg-danger/10 flex items-center justify-center">
                            <i data-feather="alert-triangle" class="w-5 h-5 text-danger"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-slate-500 text-sm">Perlu Tindak Lanjut</div>
                            <div class="text-2xl font-medium mt-1">{{ $ringkasan['perlu_tindak_lanjut'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($activeWidgetLabel)
                <div class="alert alert-primary-soft show flex flex-col sm:flex-row sm:items-center gap-3 mb-5" role="alert">
                    <div class="flex items-center mr-auto">
                        <i data-feather="info" class="w-5 h-5 mr-2"></i>
                        <span>Widget aktif: {{ $activeWidgetLabel }} untuk Semester {{ $semesterRingkasan }} / {{ $tahunAjaranRingkasan }}.</span>
                    </div>
                    <a href="{{ route('pemeriksaan.index', $resetWidgetQuery) }}" class="btn btn-outline-primary h-9 px-3">Reset Widget</a>
                </div>
            @endif

            <div class="box p-5">
                <form action="{{ route('pemeriksaan.index') }}" method="GET" class="grid grid-cols-12 gap-4 mb-5">
                    <div class="col-span-12 xl:col-span-3">
                        <label for="search" class="form-label">Cari Siswa/Guru</label>
                        <input id="search" name="search" value="{{ request('search') }}" type="text" class="form-control" placeholder="Masukkan nama...">
                    </div>
                    <div class="col-span-6 xl:col-span-2">
                        <label for="jenjang_id" class="form-label">Jenjang</label>
                        <select id="jenjang_id" name="jenjang_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach ($jenjang as $item)
                                <option value="{{ $item->id }}" {{ request('jenjang_id') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-2">
                        <label for="kelas" class="form-label">Kelas</label>
                        <select id="kelas" name="kelas" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($kelasOptions as $kelas)
                                <option value="{{ $kelas }}" {{ request('kelas') === $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-2">
                        <label for="semester" class="form-label">Semester</label>
                        <select id="semester" name="semester" class="form-select">
                            <option value="">Semua</option>
                            <option value="1" {{ request('semester') === '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{ request('semester') === '2' ? 'selected' : '' }}>2</option>
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-2">
                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                        <select id="tahun_ajaran" name="tahun_ajaran" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($tahunOptions as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun_ajaran') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-1 flex items-end">
                        <button type="submit" class="btn btn-primary w-10 h-10 p-0 tooltip" title="Cari data" aria-label="Cari data">
                            <i data-feather="search" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="table table-report -mt-2">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Siswa/Guru</th>
                                <th class="whitespace-nowrap">Semester</th>
                                <th class="whitespace-nowrap">Tahun</th>
                                <th class="whitespace-nowrap">BMI</th>
                                <th class="whitespace-nowrap">Pendengaran</th>
                                <th class="whitespace-nowrap">Gigi</th>
                                <th class="whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftarRaport as $row)
                                @php
                                    $item = $row->pemeriksaan->first();
                                @endphp
                                <tr class="intro-x">
                                    <td>
                                        <a href="{{ route('anggota.show', $row) }}" class="font-medium text-primary whitespace-nowrap">{{ $row->nama }}</a>
                                        <div class="text-slate-500 text-xs mt-0.5">{{ ucfirst(str_replace('_', ' ', $row->tipe)) }}{{ $row->kelas ? ' - ' . $row->kelas : '' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap">{{ $item->semester ?? $semesterRingkasan }}</td>
                                    <td class="whitespace-nowrap">{{ $item->tahun_ajaran ?? $tahunAjaranRingkasan }}</td>
                                    <td class="whitespace-nowrap">{{ $item->bmi ?? '-' }}</td>
                                    <td class="whitespace-nowrap">{{ $item && $item->pendengaran ? ucfirst($item->pendengaran) : '-' }}</td>
                                    <td class="whitespace-nowrap">{{ $item && $item->kondisi_gigi ? str_replace('_', ' ', ucfirst($item->kondisi_gigi)) : '-' }}</td>
                                    <td class="whitespace-nowrap">
                                        @if ($item)
                                            <span class="px-2 py-1 rounded bg-success/10 text-success text-xs font-medium">Sudah diperiksa</span>
                                        @else
                                            <span class="px-2 py-1 rounded bg-warning/10 text-warning text-xs font-medium">Belum diperiksa</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-slate-500 py-6">Belum ada data siswa/guru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-5">
                    <div class="text-slate-500">Menampilkan {{ $daftarRaport->firstItem() ?: 0 }} sampai {{ $daftarRaport->lastItem() ?: 0 }} dari {{ $daftarRaport->total() }} data</div>
                    <div>{{ $daftarRaport->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-tambah-pemeriksaan" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="{{ route('pemeriksaan.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Tambah Pemeriksaan</h2>
                    </div>
                    <div class="modal-body p-5">
                        @include('pages.pemeriksaan._form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
