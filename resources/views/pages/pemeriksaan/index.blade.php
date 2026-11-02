@extends('../layout/' . $layout)

@section('subhead')
    <title>Raport Kesehatan - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $periode = ['semester' => $semesterRingkasan, 'tahun_ajaran' => $tahunAjaranRingkasan];
        $baseQuery = array_merge(request()->except(['page']), $periode);
        $chipQuery = fn (array $override) => array_filter(array_merge($baseQuery, $override), fn ($value) => $value !== null && $value !== '');
        $widgets = [
            ['sudah', 'siswa', 'Siswa sudah diperiksa', $ringkasan['siswa_diperiksa'], 'users', 'text-success', 'bg-success/10'],
            ['belum', 'siswa', 'Siswa belum diperiksa', $ringkasan['siswa_belum_diperiksa'], 'user-x', 'text-warning', 'bg-warning/10'],
            ['sudah', 'guru', 'Guru sudah diperiksa', $ringkasan['guru_diperiksa'], 'user-check', 'text-success', 'bg-success/10'],
            ['belum', 'guru', 'Guru belum diperiksa', $ringkasan['guru_belum_diperiksa'], 'user-minus', 'text-warning', 'bg-warning/10'],
        ];
        $totalSiswa = $ringkasan['siswa_diperiksa'] + $ringkasan['siswa_belum_diperiksa'];
        $cakupan = $totalSiswa > 0 ? round($ringkasan['siswa_diperiksa'] / $totalSiswa * 100) : 0;
    @endphp

    <div class="intro-y flex flex-col sm:flex-row sm:items-center gap-3 mt-8">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">Raport Kesehatan</h2>
            <div class="text-slate-500 text-sm mt-1">
                Semester {{ $semesterRingkasan }} · Tahun ajaran {{ $tahunAjaranRingkasan }}/{{ $tahunAjaranRingkasan + 1 }}
                @if ($isPeriodeBerjalan)
                    <span class="badge-status bg-primary/10 text-primary ml-1">Periode berjalan</span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="dropdown">
                <button class="dropdown-toggle btn btn-outline-secondary h-10 px-4 gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-feather="download" class="w-4 h-4"></i> Export
                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                </button>
                <div class="dropdown-menu w-48">
                    <ul class="dropdown-content">
                        <li><a href="{{ route('export.pemeriksaan', array_merge($baseQuery, ['format' => 'excel'])) }}" data-download class="dropdown-item"><i data-feather="file-text" class="w-4 h-4 mr-2"></i>Excel</a></li>
                        <li><a href="{{ route('export.pemeriksaan', array_merge($baseQuery, ['format' => 'pdf'])) }}" data-download class="dropdown-item"><i data-feather="file" class="w-4 h-4 mr-2"></i>PDF</a></li>
                    </ul>
                </div>
            </div>
            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan" class="btn btn-primary h-10 px-4 gap-2 shadow-md">
                <i data-feather="plus" class="w-4 h-4"></i> Input Raport
            </button>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-12 gap-4 mt-5">
        <div class="intro-y col-span-12 lg:col-span-4 box p-5">
            <div class="flex items-center">
                <div class="text-slate-500 text-sm">Cakupan raport siswa</div>
                <div class="ml-auto font-medium text-2xl">{{ $cakupan }}%</div>
            </div>
            <div class="w-full h-2 bg-slate-100 dark:bg-darkmode-400 rounded mt-3">
                <div class="h-full bg-primary rounded" style="width: {{ $cakupan }}%"></div>
            </div>
            <div class="flex text-xs text-slate-500 mt-3">
                <span>{{ $ringkasan['total'] }} raport tercatat</span>
                <span class="ml-auto flex items-center {{ $ringkasan['perlu_tindak_lanjut'] ? 'text-danger' : '' }}">
                    <i data-feather="alert-triangle" class="w-3.5 h-3.5 mr-1"></i>
                    {{ $ringkasan['perlu_tindak_lanjut'] }} perlu tindak lanjut
                </span>
            </div>
        </div>
        @foreach ($widgets as [$status, $tipe, $label, $jumlah, $icon, $textClass, $bgClass])
            @php $active = $statusPemeriksaan === $status && $tipeAnggota === $tipe; @endphp
            <a href="{{ route('pemeriksaan.index', $active ? $chipQuery(['status_pemeriksaan' => null, 'tipe_anggota' => null]) : $chipQuery(['status_pemeriksaan' => $status, 'tipe_anggota' => $tipe])) }}"
               class="intro-y stat-card col-span-6 lg:col-span-2 box p-4 border {{ $active ? 'border-primary bg-primary/5' : 'border-transparent' }}"
               aria-pressed="{{ $active ? 'true' : 'false' }}">
                <div class="w-9 h-9 rounded-full {{ $bgClass }} flex items-center justify-center">
                    <i data-feather="{{ $icon }}" class="w-4 h-4 {{ $textClass }}"></i>
                </div>
                <div class="text-2xl font-medium mt-3">{{ $jumlah }}</div>
                <div class="text-slate-500 text-xs mt-0.5">{{ $label }}</div>
            </a>
        @endforeach
    </div>

    <div class="intro-y box p-5 mt-5">
        <form action="{{ route('pemeriksaan.index') }}" method="GET" class="grid grid-cols-12 gap-3" role="search">
            <input type="hidden" name="status_pemeriksaan" value="{{ $statusPemeriksaan }}">
            <input type="hidden" name="tipe_anggota" value="{{ $tipeAnggota }}">
            <div class="col-span-12 lg:col-span-4">
                <label for="search" class="sr-only">Cari nama atau NIS</label>
                <div class="relative">
                    <i data-feather="search" class="w-4 h-4 absolute inset-y-0 my-auto left-3 text-slate-400"></i>
                    <input id="search" name="search" value="{{ request('search') }}" type="search" class="form-control pl-9" placeholder="Cari nama atau NIS/NIP, tekan Enter">
                </div>
            </div>
            <div class="col-span-6 lg:col-span-2">
                <label for="jenjang_id" class="sr-only">Jenjang</label>
                <select id="jenjang_id" name="jenjang_id" class="form-select" data-auto-submit>
                    <option value="">Semua jenjang</option>
                    @foreach ($jenjang as $item)
                        <option value="{{ $item->id }}" @if (request('jenjang_id') == $item->id) selected @endif>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 lg:col-span-2">
                <label for="kelas" class="sr-only">Kelas</label>
                <select id="kelas" name="kelas" class="form-select" data-auto-submit>
                    <option value="">Semua kelas</option>
                    @foreach ($kelasOptions as $kelas)
                        <option value="{{ $kelas }}" @if (request('kelas') === $kelas) selected @endif>Kelas {{ $kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 lg:col-span-2">
                <label for="semester" class="sr-only">Semester</label>
                <select id="semester" name="semester" class="form-select" data-auto-submit>
                    @foreach ([1, 2] as $semester)
                        <option value="{{ $semester }}" @if ($semesterRingkasan === $semester) selected @endif>Semester {{ $semester }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 lg:col-span-2">
                <label for="tahun_ajaran" class="sr-only">Tahun ajaran</label>
                <select id="tahun_ajaran" name="tahun_ajaran" class="form-select" data-auto-submit>
                    @foreach ($tahunOptions as $tahun)
                        <option value="{{ $tahun }}" @if ($tahunAjaranRingkasan === $tahun) selected @endif>{{ $tahun }}/{{ $tahun + 1 }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="text-xs text-slate-500 mr-1">Status:</span>
            @foreach ([null => 'Semua', 'sudah' => 'Sudah diperiksa', 'belum' => 'Belum diperiksa'] as $key => $label)
                <a href="{{ route('pemeriksaan.index', $chipQuery(['status_pemeriksaan' => $key ?: null])) }}" class="filter-chip {{ $statusPemeriksaan == $key ? 'filter-chip--active' : '' }}">{{ $label }}</a>
            @endforeach
            <span class="text-xs text-slate-500 ml-3 mr-1">Tipe:</span>
            @foreach ([null => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru'] as $key => $label)
                <a href="{{ route('pemeriksaan.index', $chipQuery(['tipe_anggota' => $key ?: null])) }}" class="filter-chip {{ $tipeAnggota == $key ? 'filter-chip--active' : '' }}">{{ $label }}</a>
            @endforeach
            @if (request()->hasAny(['search', 'jenjang_id', 'kelas', 'status_pemeriksaan', 'tipe_anggota', 'semester', 'tahun_ajaran']))
                <a href="{{ route('pemeriksaan.index') }}" class="ml-auto text-sm text-primary flex items-center">
                    <i data-feather="x" class="w-4 h-4 mr-1"></i> Reset filter
                </a>
            @endif
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Siswa / Guru</th>
                        <th class="whitespace-nowrap">BB / TB</th>
                        <th class="whitespace-nowrap">BMI</th>
                        <th class="whitespace-nowrap">Pendengaran</th>
                        <th class="whitespace-nowrap">Gigi</th>
                        <th class="whitespace-nowrap">Status</th>
                        <th class="whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarRaport as $row)
                        @php $item = $row->pemeriksaan->first(); @endphp
                        <tr class="intro-x">
                            <td>
                                <a href="{{ route('anggota.show', $row) }}" class="font-medium text-primary whitespace-nowrap">{{ $row->nama }}</a>
                                <div class="text-slate-500 text-xs mt-0.5 whitespace-nowrap">{{ $row->tipe_label }} · {{ optional($row->jenjang)->nama ?? '-' }}{{ $row->kelas ? ' / ' . $row->kelas : '' }}</div>
                            </td>
                            <td class="whitespace-nowrap">{{ $item ? ($item->berat_badan ?? '-') . ' kg / ' . ($item->tinggi_badan ?? '-') . ' cm' : '-' }}</td>
                            <td class="whitespace-nowrap">{{ optional($item)->bmi ?? '-' }}</td>
                            <td class="whitespace-nowrap">
                                @if ($item && $item->pendengaran)
                                    <span class="{{ in_array($item->pendengaran, ['kurang', 'tuli'], true) ? 'text-danger font-medium' : '' }}">{{ ucfirst($item->pendengaran) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($item && $item->kondisi_gigi)
                                    <span class="{{ $item->kondisi_gigi !== 'baik' ? 'text-danger font-medium' : '' }}">{{ ucfirst(str_replace('_', ' ', $item->kondisi_gigi)) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($item)
                                    <span class="badge-status bg-success/10 text-success">Sudah diperiksa</span>
                                @else
                                    <span class="badge-status bg-warning/10 text-warning">Belum diperiksa</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    @if ($item)
                                        <a href="{{ route('pemeriksaan.show', $item) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                        <a href="{{ route('pemeriksaan.raport', $item) }}" data-download class="btn btn-sm btn-outline-secondary tooltip" title="Unduh raport PDF" aria-label="Unduh raport PDF">
                                            <i data-feather="download" class="w-4 h-4"></i>
                                        </a>
                                    @elseif ($isPeriodeBerjalan)
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan"
                                                data-pick-anggota data-anggota-id="{{ $row->id }}" data-anggota-tipe="{{ $row->tipe }}" data-anggota-label="{{ $row->picker_label }}">
                                            <i data-feather="plus" class="w-4 h-4 mr-1"></i> Input
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">Periode lalu</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-500 py-10">
                                <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-400"></i>
                                Tidak ada data yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5">
            <div class="text-slate-500 text-sm">Menampilkan {{ $daftarRaport->firstItem() ?: 0 }}–{{ $daftarRaport->lastItem() ?: 0 }} dari {{ $daftarRaport->total() }} data</div>
            <div>{{ $daftarRaport->links() }}</div>
        </div>
    </div>

    @include('pages.pemeriksaan._modal', ['redirectTo' => 'pemeriksaan.index', 'pickerTipe' => 'pemeriksaan'])
@endsection
