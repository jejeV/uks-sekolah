@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $statusMeta = [
            'ringan' => ['label' => 'Ringan', 'color' => 'bg-success', 'text' => 'text-success'],
            'sedang' => ['label' => 'Sedang', 'color' => 'bg-warning', 'text' => 'text-warning'],
            'berat' => ['label' => 'Berat', 'color' => 'bg-danger', 'text' => 'text-danger'],
            'dirujuk' => ['label' => 'Dirujuk', 'color' => 'bg-pending', 'text' => 'text-pending'],
        ];
        $maxTrend = max(1, $kunjungan_harian->max('total') ?? 1);
        $kasusPrioritas = ($kunjungan_per_status['berat'] ?? 0) + ($kunjungan_per_status['dirujuk'] ?? 0);
        $kunjunganNaik = $perubahan_kunjungan >= 0;
        $dashboardFilter = array_filter(request()->only(['jenjang_id', 'kelas']));
    @endphp

    @if (session('success'))
        <div class="alert alert-success show flex items-center mt-6" role="alert">
            <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger show flex items-start mt-6" role="alert">
            <i data-feather="alert-circle" class="w-5 h-5 mr-2 mt-0.5"></i>
            <div>
                <div class="font-medium">Data belum dapat disimpan.</div>
                <div class="text-sm mt-1">{{ $errors->first() }}</div>
            </div>
        </div>
    @endif

    <div id="dashboard-loading" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/35">
        <div class="box px-5 py-4 flex items-center gap-3 shadow-lg">
            <i data-feather="loader" class="w-5 h-5 animate-spin text-primary"></i>
            <span class="font-medium">Memuat data...</span>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex flex-col lg:flex-row lg:items-center gap-4">
                        <div class="mr-auto">
                            <div class="text-slate-500 text-sm">{{ now()->translatedFormat('l, d F Y') }}</div>
                            <h1 class="text-2xl font-medium mt-1">Dashboard Pelayanan UKS</h1>
                            <div class="text-slate-500 mt-1">Pantau pelayanan harian dan kesiapan raport kesehatan siswa.</div>
                        </div>
                        <div class="flex items-center">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah"
                                    class="btn btn-primary h-10 px-4 gap-2 shadow-md">
                                <i data-feather="plus" class="w-4 h-4"></i>
                                Kunjungan
                            </button>
                        </div>
                    </div>

                    <div class="intro-y mt-5">
                        <div class="flex flex-col xl:flex-row xl:items-center gap-3">
                            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-3" data-loading-form
                                  onsubmit="this.querySelectorAll('select').forEach(function(select) { select.disabled = !select.value; })">
                                <select id="dashboard-jenjang" name="jenjang_id" aria-label="Filter jenjang" class="w-44 form-select box h-10" onchange="this.form.requestSubmit()">
                                    <option value="">Semua Jenjang</option>
                                    @foreach ($jenjang as $item)
                                        <option value="{{ $item->id }}" {{ request('jenjang_id') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                                    @endforeach
                                </select>
                                <select id="dashboard-kelas" name="kelas" aria-label="Filter kelas" class="w-40 form-select box h-10">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelasOptions as $kelas)
                                        <option value="{{ $kelas }}" {{ request('kelas') === $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                                    @endforeach
                                </select>
                                <div class="relative text-slate-500">
                                    <i data-feather="calendar" class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0"></i>
                                    <input id="dashboard-report-date" type="text" value="{{ now()->startOfMonth()->toDateString() }} - {{ now()->toDateString() }}" class="datepicker form-control w-56 box pl-10" data-format="YYYY-MM-DD">
                                </div>
                                <button type="submit" class="btn btn-primary h-10 px-4 gap-2">
                                    <i data-feather="filter" class="w-4 h-4"></i>
                                    Terapkan Filter
                                </button>
                                <a href="{{ route('dashboard') }}" data-loading-link class="btn btn-outline-secondary h-10 px-4 gap-2 {{ $dashboardFilter ? '' : 'opacity-50 pointer-events-none' }}">
                                    <i data-feather="rotate-ccw" class="w-4 h-4"></i>
                                    Refresh
                                </a>
                            </form>
                            <div class="dropdown xl:ml-auto">
                                <button class="dropdown-toggle btn box h-10 px-4 gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-feather="download" class="w-4 h-4"></i>
                                    Export
                                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                                </button>
                                <div class="dropdown-menu w-56">
                                    <ul class="dropdown-content">
                                        <li><a href="{{ route('export.kunjungan', array_merge($dashboardFilter, ['format' => 'excel'])) }}" data-loading-link data-download-link class="dropdown-item"><i data-feather="file-text" class="w-4 h-4 mr-2"></i>Kunjungan Excel</a></li>
                                        <li><a href="{{ route('export.kunjungan', array_merge($dashboardFilter, ['format' => 'pdf'])) }}" data-loading-link data-download-link class="dropdown-item"><i data-feather="file" class="w-4 h-4 mr-2"></i>Kunjungan PDF</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Ringkasan UKS</h2>
                        <span class="ml-auto flex items-center text-slate-500 text-sm">
                            <i data-feather="refresh-cw" class="w-4 h-4 mr-2"></i>
                            Data terbaru
                        </span>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('kunjungan.index', array_merge($dashboardFilter, ['tanggal' => now()->toDateString()])) }}" data-loading-link class="report-box zoom-in block">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="calendar" class="report-box__icon text-primary"></i>
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-success tooltip" title="Kunjungan yang tercatat hari ini">
                                                Hari ini
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $kunjungan_hari_ini }}</div>
                                    <div class="text-base text-slate-500 mt-1">Kunjungan UKS</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('kunjungan.index', $dashboardFilter) }}" data-loading-link class="report-box zoom-in block">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="activity" class="report-box__icon text-pending"></i>
                                        <div class="ml-auto">
                                            <div class="report-box__indicator {{ $kunjunganNaik ? 'bg-success' : 'bg-danger' }} tooltip"
                                                 title="Perbandingan dengan bulan sebelumnya">
                                                {{ abs($perubahan_kunjungan) }}%
                                                <i data-feather="{{ $kunjunganNaik ? 'chevron-up' : 'chevron-down' }}" class="w-4 h-4 ml-0.5"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $kunjungan_bulan }}</div>
                                    <div class="text-base text-slate-500 mt-1">Kunjungan Bulan Ini</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('pemeriksaan.index', ['semester' => $semester_berjalan, 'tahun_ajaran' => $tahun_ajaran_berjalan]) }}"
                               data-loading-link class="report-box zoom-in block">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="check-square" class="report-box__icon text-success"></i>
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-success tooltip" title="Cakupan raport kesehatan semester berjalan">
                                                {{ $cakupan_pemeriksaan_semester }}%
                                                <i data-feather="check" class="w-4 h-4 ml-0.5"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $pemeriksaan_semester }}</div>
                                    <div class="text-base text-slate-500 mt-1">Raport Selesai</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('pemeriksaan.index', ['semester' => $semester_berjalan, 'tahun_ajaran' => $tahun_ajaran_berjalan, 'status_pemeriksaan' => 'belum', 'tipe_anggota' => 'siswa']) }}" data-loading-link class="report-box zoom-in block">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-feather="alert-triangle" class="report-box__icon text-danger"></i>
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-danger tooltip" title="Siswa yang perlu dijadwalkan raport kesehatan">
                                                Prioritas
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $belum_pemeriksaan_semester }}</div>
                                    <div class="text-base text-slate-500 mt-1">Belum Raport</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-8 mt-4">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Tren Kunjungan</h2>
                        <span class="ml-auto text-slate-500 text-sm">{{ $total_trend_kunjungan }} kunjungan dalam 7 hari</span>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        <div class="flex items-end gap-3 h-56">
                            @foreach ($kunjungan_harian as $hari)
                                <a href="{{ route('kunjungan.index', ['tanggal' => $hari['date']]) }}"
                                   data-loading-link
                                   class="flex-1 h-full flex flex-col justify-end items-center group"
                                   title="{{ $hari['total'] }} kunjungan pada {{ $hari['label'] }}">
                                    <div class="text-xs font-medium mb-2">{{ $hari['total'] }}</div>
                                    <div class="w-full bg-primary/80 group-hover:bg-primary rounded-t transition-colors"
                                         style="height: {{ max(6, ($hari['total'] / $maxTrend) * 100) }}%"></div>
                                    <div class="text-xs text-slate-500 mt-3 whitespace-nowrap">{{ $hari['label'] }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-4 mt-4">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Status Kunjungan</h2>
                        <span class="ml-auto text-slate-500 text-sm">{{ $kunjungan_bulan }} total</span>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        @foreach ($status_kunjungan as $item)
                            @php $meta = $statusMeta[$item['status']]; @endphp
                            <a href="{{ route('kunjungan.index', array_merge($dashboardFilter, ['status' => $item['status']])) }}"
                               data-loading-link
                               class="block {{ !$loop->first ? 'mt-5' : '' }}">
                                <div class="flex items-center text-sm">
                                    <div class="w-2 h-2 rounded-full {{ $meta['color'] }} mr-3"></div>
                                    <span class="font-medium">{{ $meta['label'] }}</span>
                                    <span class="ml-auto text-slate-500">{{ $item['total'] }} data</span>
                                    <span class="w-10 text-right font-medium">{{ $item['persen'] }}%</span>
                                </div>
                                <div class="w-full h-2 bg-slate-100 dark:bg-darkmode-400 rounded mt-2">
                                    <div class="h-full {{ $meta['color'] }} rounded" style="width: {{ $item['persen'] }}%"></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="col-span-12 mt-4">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Kunjungan Terbaru</h2>
                        <a href="{{ route('kunjungan.index', $dashboardFilter) }}" data-loading-link class="ml-auto text-primary text-sm">Lihat semua</a>
                    </div>
                    <div class="intro-y box mt-5 overflow-auto">
                        <table class="table table-report">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Nama</th>
                                    <th class="whitespace-nowrap">Tanggal</th>
                                    <th class="whitespace-nowrap">Keluhan</th>
                                    <th class="whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kunjungan_terbaru as $item)
                                    <tr class="intro-x">
                                        <td>
                                            @if ($item->anggota)
                                                <a href="{{ route('anggota.show', $item->anggota) }}" data-loading-link class="font-medium text-primary whitespace-nowrap">{{ $item->anggota->nama }}</a>
                                            @else
                                                -
                                            @endif
                                            <div class="text-xs text-slate-500 mt-0.5">{{ optional(optional($item->anggota)->jenjang)->nama ?? '-' }}</div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $item->tanggal->format('d/m/Y') }}</td>
                                        <td><div class="truncate max-w-sm">{{ $item->keluhan }}</div></td>
                                        <td>
                                            @php $meta = $statusMeta[$item->status] ?? $statusMeta['ringan']; @endphp
                                            <span class="text-xs font-medium capitalize {{ $meta['text'] }}">{{ $item->status }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-slate-500 py-8">Belum ada kunjungan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-6">
                    <div class="col-span-12 md:col-span-6 2xl:col-span-12 mt-8">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Prioritas Raport Kesehatan</h2>
                            <span class="ml-auto text-danger font-medium">{{ $belum_pemeriksaan_semester }}</span>
                        </div>
                        <div class="intro-x box p-5 mt-5">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-slate-500 text-xs">Semester {{ $semester_berjalan }} / {{ $tahun_ajaran_berjalan }}</div>
                                    <div class="text-2xl font-medium mt-1">{{ $cakupan_pemeriksaan_semester }}%</div>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-darkmode-400 rounded mt-4">
                                <div class="h-full bg-primary rounded" style="width: {{ min(100, $cakupan_pemeriksaan_semester) }}%"></div>
                            </div>
                            <div class="border-t border-slate-200/60 dark:border-darkmode-400 mt-5 pt-2">
                                @forelse ($mcu_belum_list as $siswa)
                                    <div class="flex items-center py-3">
                                        <div class="w-9 h-9 flex-none rounded-full bg-warning/10 flex items-center justify-center">
                                            <i data-feather="user" class="w-4 h-4 text-warning"></i>
                                        </div>
                                        <div class="ml-3 min-w-0">
                                            <a href="{{ route('anggota.show', $siswa) }}" data-loading-link class="font-medium text-primary truncate block">{{ $siswa->nama }}</a>
                                            <div class="text-xs text-slate-500 truncate">{{ optional($siswa->jenjang)->nama ?? '-' }}{{ $siswa->kelas ? ' - ' . $siswa->kelas : '' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-slate-500 py-6">Semua siswa sudah memiliki raport kesehatan.</div>
                                @endforelse
                            </div>
                            <a href="{{ route('pemeriksaan.index', ['semester' => $semester_berjalan, 'tahun_ajaran' => $tahun_ajaran_berjalan]) }}"
                               data-loading-link
                               class="btn btn-outline-secondary w-full mt-3">
                                Lihat Data Raport
                            </a>
                        </div>
                    </div>

                    <div class="col-span-12 md:col-span-6 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Menu Cepat</h2>
                        </div>
                        <div class="intro-x box p-5 mt-5">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah"
                                    class="btn btn-outline-primary w-full justify-start">
                                <i data-feather="plus-circle" class="w-4 h-4 mr-3"></i>
                                Tambah Kunjungan
                            </button>
                            <a href="{{ route('anggota.index') }}" data-loading-link class="btn btn-outline-secondary w-full justify-start mt-3">
                                <i data-feather="users" class="w-4 h-4 mr-3"></i>
                                Data Anggota
                            </a>
                            <a href="{{ route('kunjungan.index', ['prioritas' => 'tinggi']) }}"
                               data-loading-link
                               class="btn btn-outline-danger w-full justify-start mt-3">
                                <i data-feather="alert-triangle" class="w-4 h-4 mr-3"></i>
                                Kasus Prioritas
                                <span class="ml-auto">{{ $kasusPrioritas }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.dashboard._input-modals')
@endsection

@section('script')
    @include('pages.dashboard._input-script')
@endsection
