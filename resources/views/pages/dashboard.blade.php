@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $statusMeta = [
            'ringan' => ['label' => 'Ringan', 'icon' => 'check-circle', 'bg' => 'bg-success', 'soft' => 'bg-success/10', 'text' => 'text-success'],
            'sedang' => ['label' => 'Sedang', 'icon' => 'alert-circle', 'bg' => 'bg-warning', 'soft' => 'bg-warning/10', 'text' => 'text-warning'],
            'berat' => ['label' => 'Berat', 'icon' => 'activity', 'bg' => 'bg-danger', 'soft' => 'bg-danger/10', 'text' => 'text-danger'],
            'dirujuk' => ['label' => 'Dirujuk', 'icon' => 'navigation', 'bg' => 'bg-pending', 'soft' => 'bg-pending/10', 'text' => 'text-pending'],
        ];
        $maxTrend = max(1, collect($kunjungan_harian)->max('total') ?? 1);
        $maxJenjang = max(1, $jenjang_stat->max('total_aktif') ?? 1);
        $selisihKunjungan = $kunjungan_bulan - $kunjungan_bulan_lalu;
        $kasusPrioritas = ($kunjungan_per_status['berat'] ?? 0) + ($kunjungan_per_status['dirujuk'] ?? 0);
        $metricCards = [
            [
                'label' => 'Siswa Aktif',
                'value' => $total_siswa,
                'hint' => 'Terdaftar dan aktif',
                'icon' => 'users',
                'soft' => 'bg-primary/10',
                'text' => 'text-primary',
                'insight' => 'insight-anggota-tipe-siswa',
            ],
            [
                'label' => 'Guru & Staff',
                'value' => $total_guru,
                'hint' => 'Pendamping layanan',
                'icon' => 'user-check',
                'soft' => 'bg-pending/10',
                'text' => 'text-pending',
                'insight' => 'insight-anggota-petugas',
            ],
            [
                'label' => 'Kunjungan Bulan Ini',
                'value' => $kunjungan_bulan,
                'hint' => ($selisihKunjungan >= 0 ? '+' : '') . $selisihKunjungan . ' dari bulan lalu',
                'icon' => 'calendar',
                'soft' => 'bg-success/10',
                'text' => $selisihKunjungan >= 0 ? 'text-success' : 'text-danger',
                'insight' => 'insight-kunjungan-bulan',
            ],
            [
                'label' => 'Prioritas MCU',
                'value' => $belum_pemeriksaan_semester,
                'hint' => $cakupan_pemeriksaan_semester . '% selesai semester ini',
                'icon' => 'clipboard',
                'soft' => 'bg-warning/10',
                'text' => 'text-warning',
                'insight' => 'insight-mcu',
            ],
        ];
    @endphp

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">

                @if (session('success'))
                    <div class="col-span-12 mt-5">
                        <div class="alert alert-success show flex items-center" role="alert">
                            <i data-feather="check-circle" class="w-6 h-6 mr-2"></i>
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <div class="col-span-12 mt-8">
                    <div class="intro-y flex flex-col xl:flex-row xl:items-end gap-4">
                        <div class="mr-auto min-w-0">
                            <div class="text-slate-500 text-sm">{{ now()->translatedFormat('l, d F Y') }}</div>
                            <h1 class="text-2xl font-medium mt-1">Dashboard Pelayanan UKS</h1>
                            <div class="text-slate-500 mt-2 max-w-2xl">
                                Pantau layanan harian, status kasus, dan cakupan pemeriksaan kesehatan siswa secara ringkas.
                            </div>
                        </div>
                        <div class="flex flex-nowrap items-center gap-2 w-full xl:w-auto xl:flex-none">
                            <button data-tw-toggle="modal" data-tw-target="#modal-tambah" class="btn btn-primary shadow-md h-10 w-32 flex-none px-3 justify-center text-sm gap-2">
                                <i data-feather="plus" class="w-4 h-4"></i> Kunjungan
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan" class="btn box text-slate-600 dark:text-slate-300 h-10 w-32 flex-none px-3 justify-center text-sm gap-2">
                                <i data-feather="clipboard" class="w-4 h-4"></i> Periksa
                            </button>
                            <div class="dropdown flex-none">
                                <button class="dropdown-toggle btn box text-slate-600 dark:text-slate-300 h-10 w-32 px-3 justify-center text-sm gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-feather="file-text" class="w-4 h-4"></i> Export
                                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                                </button>
                                <div class="dropdown-menu w-72">
                                    <ul class="dropdown-content">
                                        <li class="px-3 py-2 text-xs uppercase tracking-wide text-slate-500">Riwayat Kunjungan UKS</li>
                                        <li>
                                            <a href="{{ route('export.kunjungan', ['format' => 'excel']) }}" class="dropdown-item">
                                                <i data-feather="file-text" class="w-4 h-4 mr-2"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('export.kunjungan', ['format' => 'pdf']) }}" class="dropdown-item">
                                                <i data-feather="file" class="w-4 h-4 mr-2"></i> Export PDF
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li class="px-3 py-2 text-xs uppercase tracking-wide text-slate-500">Riwayat Penyakit</li>
                                        <li>
                                            <a href="{{ route('export.riwayat', ['format' => 'excel']) }}" class="dropdown-item">
                                                <i data-feather="file-text" class="w-4 h-4 mr-2"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('export.riwayat', ['format' => 'pdf']) }}" class="dropdown-item">
                                                <i data-feather="file" class="w-4 h-4 mr-2"></i> Export PDF
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li class="px-3 py-2 text-xs uppercase tracking-wide text-slate-500">Raport Kesehatan</li>
                                        <li>
                                            <a href="{{ route('export.pemeriksaan', ['format' => 'excel']) }}" class="dropdown-item">
                                                <i data-feather="file-text" class="w-4 h-4 mr-2"></i> Export Excel
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('export.pemeriksaan', ['format' => 'pdf']) }}" class="dropdown-item">
                                                <i data-feather="file" class="w-4 h-4 mr-2"></i> Export PDF
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-5 mt-5">
                        @foreach ($metricCards as $card)
                            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                                <button type="button"
                                        data-tw-toggle="modal"
                                        data-tw-target="#modal-dashboard-insight"
                                        data-insight-title="{{ $card['label'] }}"
                                        data-insight-template="{{ $card['insight'] }}"
                                        class="report-box zoom-in block w-full text-left">
                                    <div class="box p-5 min-h-[150px]">
                                        <div class="flex items-start">
                                            <div class="w-11 h-11 rounded-md {{ $card['soft'] }} flex items-center justify-center">
                                                <i data-feather="{{ $card['icon'] }}" class="w-5 h-5 {{ $card['text'] }}"></i>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <div class="text-3xl font-medium leading-8">{{ $card['value'] }}</div>
                                                <div class="text-xs {{ $card['text'] }} mt-1">{{ $card['hint'] }}</div>
                                            </div>
                                        </div>
                                        <div class="text-base font-medium mt-6">{{ $card['label'] }}</div>
                                        <div class="text-slate-500 text-xs mt-1 flex items-center">
                                            Buka ringkasan cepat
                                            <i data-feather="maximize-2" class="w-3.5 h-3.5 ml-1"></i>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-span-12 xl:col-span-8 mt-6">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Tren Kunjungan 7 Hari</h2>
                        <div class="ml-auto text-slate-500 text-sm">{{ $kunjungan_minggu_ini }} kunjungan minggu ini</div>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        <div class="flex items-end gap-3 h-64">
                            @foreach ($kunjungan_harian as $hari)
                                <button type="button"
                                   data-tw-toggle="modal"
                                   data-tw-target="#modal-dashboard-insight"
                                   data-insight-title="Kunjungan {{ $hari['label'] }}"
                                   data-insight-template="insight-kunjungan-tanggal-{{ $hari['date'] }}"
                                   class="flex-1 h-full flex flex-col justify-end items-center group"
                                   title="Lihat kunjungan tanggal {{ $hari['label'] }}">
                                    <div class="text-xs font-medium mb-2">{{ $hari['total'] }}</div>
                                    <div class="w-full rounded-t bg-primary/80 group-hover:bg-primary transition-colors" style="height: {{ max(8, ($hari['total'] / $maxTrend) * 100) }}%"></div>
                                </button>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-3 border-t border-slate-200/60 dark:border-darkmode-400 pt-3 mt-4 text-center text-xs text-slate-500">
                            @foreach ($kunjungan_harian as $hari)
                                <button type="button"
                                        data-tw-toggle="modal"
                                        data-tw-target="#modal-dashboard-insight"
                                        data-insight-title="Kunjungan {{ $hari['label'] }}"
                                        data-insight-template="insight-kunjungan-tanggal-{{ $hari['date'] }}"
                                        class="truncate hover:text-primary">{{ $hari['label'] }}</button>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Total Kunjungan 7 Hari" data-insight-template="insight-kunjungan-trend" class="text-left border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="text-slate-500 text-xs">Total 7 hari</div>
                                <div class="font-medium mt-1">{{ $total_trend_kunjungan }} kunjungan</div>
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Hari Tersibuk" data-insight-template="insight-kunjungan-tanggal-{{ $hari_tersibuk['date'] ?? now()->toDateString() }}" class="text-left border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="text-slate-500 text-xs">Hari tersibuk</div>
                                <div class="font-medium mt-1">{{ $hari_tersibuk['label'] ?? '-' }} - {{ $hari_tersibuk['total'] ?? 0 }} kunjungan</div>
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Total Kunjungan 7 Hari" data-insight-template="insight-kunjungan-trend" class="text-left border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="text-slate-500 text-xs">Rata-rata harian</div>
                                <div class="font-medium mt-1">{{ $rata_kunjungan_harian }} kunjungan</div>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 xl:col-span-4 mt-6">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Status Bulan Ini</h2>
                        <div class="ml-auto text-slate-500 text-sm">{{ $kunjungan_bulan }} total</div>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        @foreach ($status_kunjungan as $item)
                            @php $meta = $statusMeta[$item['status']] ?? $statusMeta['ringan']; @endphp
                            <button type="button"
                               data-tw-toggle="modal"
                               data-tw-target="#modal-dashboard-insight"
                               data-insight-title="Kunjungan {{ $meta['label'] }}"
                               data-insight-template="insight-status-{{ $item['status'] }}"
                               class="block w-full text-left rounded-md p-2 -mx-2 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors {{ !$loop->first ? 'mt-3' : '' }}">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-full {{ $meta['soft'] }} flex items-center justify-center">
                                        <i data-feather="{{ $meta['icon'] }}" class="w-4 h-4 {{ $meta['text'] }}"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium">{{ $meta['label'] }}</div>
                                        <div class="text-slate-500 text-xs">{{ $item['total'] }} kunjungan</div>
                                    </div>
                                    <div class="ml-auto flex items-center">
                                        <div class="font-medium">{{ $item['persen'] }}%</div>
                                        <i data-feather="chevron-right" class="w-4 h-4 ml-2 text-slate-400"></i>
                                    </div>
                                </div>
                                <div class="w-full h-2 bg-slate-100 dark:bg-darkmode-400 rounded mt-3">
                                    <div class="h-full rounded {{ $meta['bg'] }}" style="width: {{ $item['persen'] }}%"></div>
                                </div>
                            </button>
                        @endforeach
                        <div class="border-t border-slate-200/60 dark:border-darkmode-400 pt-4 mt-4 text-sm">
                            <div class="flex items-center">
                                <span class="text-slate-500">Dominan bulan ini</span>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Kunjungan {{ ucfirst($status_terbanyak['status'] ?? 'ringan') }}" data-insight-template="insight-status-{{ $status_terbanyak['status'] ?? 'ringan' }}" class="ml-auto font-medium text-primary capitalize">
                                    {{ $status_terbanyak['status'] ?? 'ringan' }}
                                </button>
                            </div>
                            <div class="flex items-center mt-2">
                                <span class="text-slate-500">Butuh perhatian</span>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Kasus Prioritas" data-insight-template="insight-prioritas" class="ml-auto font-medium text-danger">
                                    {{ $kasusPrioritas }} kasus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-6 mt-6">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Sebaran Anggota</h2>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        <div class="grid grid-cols-3 gap-3">
                            @foreach ($tipe_anggota_stat as $item)
                                <button type="button"
                                   data-tw-toggle="modal"
                                   data-tw-target="#modal-dashboard-insight"
                                   data-insight-title="Anggota {{ $item['label'] }}"
                                   data-insight-template="insight-anggota-tipe-{{ $item['tipe'] }}"
                                   class="text-center border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                    <div class="text-2xl font-medium">{{ $item['total'] }}</div>
                                    <div class="text-slate-500 text-xs mt-1">{{ $item['label'] }}</div>
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-5">
                            @forelse ($jenjang_stat as $item)
                                <button type="button"
                                   data-tw-toggle="modal"
                                   data-tw-target="#modal-dashboard-insight"
                                   data-insight-title="Anggota {{ $item->nama }}"
                                   data-insight-template="insight-anggota-jenjang-{{ $item->id }}"
                                   class="block w-full text-left rounded-md p-2 -mx-2 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors {{ !$loop->first ? 'mt-2' : '' }}">
                                    <div class="flex items-center text-sm">
                                        <span class="font-medium truncate">{{ $item->nama }}</span>
                                        <span class="ml-auto text-slate-500">{{ $item->total_siswa }} siswa / {{ $item->total_aktif }} anggota</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 dark:bg-darkmode-400 rounded mt-2">
                                        <div class="h-full rounded bg-primary/80" style="width: {{ ($item->total_aktif / $maxJenjang) * 100 }}%"></div>
                                    </div>
                                </button>
                            @empty
                                <div class="text-center text-slate-500 py-6">Belum ada data jenjang.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-span-12 lg:col-span-6 mt-6">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Kesiapan Pemeriksaan</h2>
                    </div>
                    <div class="intro-y box p-5 mt-5">
                        <button type="button"
                                data-tw-toggle="modal"
                                data-tw-target="#modal-dashboard-insight"
                                data-insight-title="Prioritas MCU"
                                data-insight-template="insight-mcu"
                                class="flex items-center w-full text-left rounded-md p-2 -m-2 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors">
                            <div class="min-w-0">
                                <div class="text-slate-500">Cakupan MCU tahun {{ now()->year }}</div>
                                <div class="text-3xl font-medium mt-2">{{ $pemeriksaan_tahun_ini }} siswa</div>
                                <div class="text-xs text-slate-500 mt-1">{{ $belum_pemeriksaan }} siswa belum memiliki hasil MCU tahun ini</div>
                            </div>
                            <div class="ml-auto w-24 h-24 rounded-full border-8 border-primary/20 flex items-center justify-center">
                                <div class="text-xl font-medium text-primary">{{ $cakupan_pemeriksaan }}%</div>
                            </div>
                        </button>
                        <div class="grid grid-cols-2 gap-3 mt-5">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="MCU Bulan Ini" data-insight-template="insight-mcu-terbaru" class="text-left border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="text-slate-500 text-xs">Pemeriksaan bulan ini</div>
                                <div class="font-medium mt-1">{{ $pemeriksaan_bulan }} catatan</div>
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Siswa Aktif" data-insight-template="insight-anggota-tipe-siswa" class="text-left border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="text-slate-500 text-xs">Target siswa aktif</div>
                                <div class="font-medium mt-1">{{ $total_siswa }} siswa</div>
                            </button>
                        </div>
                        <div class="border-t border-slate-200/60 dark:border-darkmode-400 mt-5 pt-5 grid grid-cols-2 gap-3">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan" class="btn btn-outline-primary justify-center">
                                <i data-feather="clipboard" class="w-4 h-4 mr-2"></i> Pemeriksaan
                            </button>
                            <a href="{{ route('pemeriksaan.index') }}" class="btn btn-outline-secondary justify-center">
                                <i data-feather="list" class="w-4 h-4 mr-2"></i> Data Raport
                            </a>
                        </div>
                    </div>
                </div>

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
                                            @if ($item->anggota)
                                                <a href="{{ route('anggota.show', $item->anggota) }}" class="font-medium whitespace-nowrap text-primary">{{ $item->anggota->nama }}</a>
                                            @else
                                                <div class="font-medium whitespace-nowrap">-</div>
                                            @endif
                                            <div class="text-slate-500 text-xs mt-0.5 capitalize">{{ optional($item->anggota)->tipe ?? '-' }}</div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ optional(optional($item->anggota)->jenjang)->nama ?? '-' }}</td>
                                        <td class="whitespace-nowrap">{{ $item->tanggal->format('d/m/Y') }}</td>
                                        <td><div class="truncate max-w-xs">{{ $item->keluhan }}</div></td>
                                        <td class="text-center">
                                            @php
                                                $badge = $statusMeta[$item->status] ?? [
                                                    'soft' => 'bg-slate-100',
                                                    'text' => 'text-slate-500',
                                                ];
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge['soft'] }} {{ $badge['text'] }} capitalize font-medium">
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
                                                   data-nama="{{ optional($item->anggota)->nama ?? '-' }}"
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

                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Prioritas Hari Ini</h2>
                        </div>
                        <div class="intro-x box p-5 mt-5">
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Kunjungan Hari Ini" data-insight-template="insight-kunjungan-hari-ini" class="flex items-center w-full text-left rounded-md p-2 -m-2 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-success/10 flex items-center justify-center">
                                    <i data-feather="calendar" class="w-5 h-5 text-success"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium">{{ $kunjungan_hari_ini }} kunjungan</div>
                                    <div class="text-slate-500 text-xs mt-0.5">Masuk hari ini</div>
                                </div>
                                <i data-feather="chevron-right" class="w-4 h-4 ml-auto text-slate-400"></i>
                            </button>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-dashboard-insight" data-insight-title="Kasus Prioritas" data-insight-template="insight-prioritas" class="flex items-center w-full text-left rounded-md p-2 -mx-2 mt-3 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-danger/10 flex items-center justify-center">
                                    <i data-feather="activity" class="w-5 h-5 text-danger"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium">{{ $kasusPrioritas }} kasus</div>
                                    <div class="text-slate-500 text-xs mt-0.5">Berat atau dirujuk bulan ini</div>
                                </div>
                                <i data-feather="chevron-right" class="w-4 h-4 ml-auto text-slate-400"></i>
                            </button>
                            <div class="border-t border-slate-200/60 dark:border-darkmode-400 mt-5 pt-5">
                                <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary w-full justify-center">
                                    <i data-feather="users" class="w-4 h-4 mr-2"></i> Kelola Anggota
                                </a>
                            </div>
                        </div>
                    </div>

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

    <div id="modal-dashboard-insight" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="dashboard-insight-title" class="font-medium text-base mr-auto">Ringkasan Dashboard</h2>
                </div>
                <div id="dashboard-insight-body" class="modal-body p-5"></div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="hidden">
        <template id="insight-mcu">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 lg:col-span-4">
                    <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
                        <div class="text-slate-500 text-xs">Periode MCU aktif</div>
                        <div class="text-xl font-medium mt-1">Semester {{ $semester_berjalan }} / {{ $tahun_ajaran_berjalan }}</div>
                    </div>
                </div>
                <div class="col-span-6 lg:col-span-4">
                    <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
                        <div class="text-slate-500 text-xs">Sudah MCU</div>
                        <div class="text-xl font-medium mt-1">{{ $pemeriksaan_semester }} siswa</div>
                    </div>
                </div>
                <div class="col-span-6 lg:col-span-4">
                    <div class="border border-warning/30 bg-warning/5 rounded-md p-4">
                        <div class="text-slate-500 text-xs">Belum MCU</div>
                        <div class="text-xl font-medium mt-1 text-warning">{{ $belum_pemeriksaan_semester }} siswa</div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="w-full h-2 bg-slate-100 dark:bg-darkmode-400 rounded">
                        <div class="h-full rounded bg-primary" style="width: {{ $cakupan_pemeriksaan_semester }}%"></div>
                    </div>
                    <div class="text-xs text-slate-500 mt-2">{{ $cakupan_pemeriksaan_semester }}% cakupan MCU semester berjalan.</div>
                </div>
                <div class="col-span-12 lg:col-span-7">
                    <div class="font-medium mb-3">Siswa Prioritas Belum MCU</div>
                    <div class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                        @forelse ($mcu_belum_list as $siswa)
                            <div class="py-3 flex items-center">
                                <div class="w-9 h-9 rounded-full bg-warning/10 flex items-center justify-center">
                                    <i data-feather="user" class="w-4 h-4 text-warning"></i>
                                </div>
                                <div class="ml-3 min-w-0">
                                    <div class="font-medium truncate">{{ $siswa->nama }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($siswa->jenjang)->nama ?? '-' }}{{ $siswa->kelas ? ' - ' . $siswa->kelas : '' }}</div>
                                </div>
                                <a href="{{ route('anggota.show', $siswa) }}" class="btn btn-sm btn-outline-primary ml-auto">Profil</a>
                            </div>
                        @empty
                            <div class="text-center text-slate-500 py-6">Semua siswa sudah memiliki hasil MCU periode ini.</div>
                        @endforelse
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-5">
                    <div class="font-medium mb-3">MCU Terbaru</div>
                    <div class="space-y-3">
                        @forelse ($mcu_terbaru as $mcu)
                            <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-3">
                                <div class="font-medium">{{ optional($mcu->anggota)->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-1">Semester {{ $mcu->semester }} / {{ $mcu->tahun_ajaran }} oleh {{ optional($mcu->petugas)->name ?? '-' }}</div>
                            </div>
                        @empty
                            <div class="text-center text-slate-500 py-6">Belum ada hasil MCU.</div>
                        @endforelse
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan" class="btn btn-primary justify-center">
                            <i data-feather="clipboard" class="w-4 h-4 mr-2"></i> Input MCU
                        </button>
                        <a href="{{ route('pemeriksaan.index', ['semester' => $semester_berjalan, 'tahun_ajaran' => $tahun_ajaran_berjalan]) }}" class="btn btn-outline-secondary justify-center">
                            <i data-feather="list" class="w-4 h-4 mr-2"></i> Data MCU
                        </a>
                    </div>
                </div>
            </div>
        </template>

        <template id="insight-mcu-terbaru">
            <div>
                <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                    <i data-feather="clipboard" class="w-5 h-5 mr-2"></i>
                    {{ $pemeriksaan_bulan }} catatan MCU dibuat pada bulan {{ now()->translatedFormat('F Y') }}.
                </div>
                @include('pages.dashboard._mcu-list', ['items' => $mcu_bulan_list, 'empty' => 'Belum ada data MCU bulan ini.'])
            </div>
        </template>

        <template id="insight-kunjungan-bulan">
            <div>
                <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                    <i data-feather="calendar" class="w-5 h-5 mr-2"></i>
                    {{ $kunjungan_bulan }} kunjungan pada bulan {{ now()->translatedFormat('F Y') }}.
                </div>
                @include('pages.dashboard._kunjungan-list', ['items' => $kunjungan_bulan_list, 'empty' => 'Belum ada kunjungan bulan ini.'])
            </div>
        </template>

        <template id="insight-kunjungan-trend">
            <div>
                <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                    <i data-feather="bar-chart-2" class="w-5 h-5 mr-2"></i>
                    {{ $total_trend_kunjungan }} kunjungan dalam 7 hari terakhir, rata-rata {{ $rata_kunjungan_harian }} per hari.
                </div>
                @include('pages.dashboard._kunjungan-list', ['items' => $kunjungan_trend_list, 'empty' => 'Belum ada kunjungan dalam 7 hari terakhir.'])
            </div>
        </template>

        <template id="insight-kunjungan-hari-ini">
            <div>
                <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                    <i data-feather="calendar" class="w-5 h-5 mr-2"></i>
                    {{ $kunjungan_hari_ini }} kunjungan pada {{ now()->translatedFormat('d F Y') }}.
                </div>
                @include('pages.dashboard._kunjungan-list', ['items' => $kunjungan_hari_ini_list, 'empty' => 'Belum ada kunjungan hari ini.'])
            </div>
        </template>

        @foreach ($kunjungan_harian as $hari)
            <template id="insight-kunjungan-tanggal-{{ $hari['date'] }}">
                <div>
                    <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                        <i data-feather="calendar" class="w-5 h-5 mr-2"></i>
                        {{ $hari['total'] }} kunjungan pada {{ \Carbon\Carbon::parse($hari['date'])->translatedFormat('d F Y') }}.
                    </div>
                    @include('pages.dashboard._kunjungan-list', ['items' => $kunjungan_trend_by_date->get($hari['date'], collect()), 'empty' => 'Tidak ada kunjungan pada tanggal ini.'])
                </div>
            </template>
        @endforeach

        @foreach ($status_kunjungan as $item)
            @php
                $meta = $statusMeta[$item['status']] ?? $statusMeta['ringan'];
                $statusItems = $kunjungan_status_detail->get($item['status'], collect());
            @endphp
            <template id="insight-status-{{ $item['status'] }}">
                <div>
                    <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                        <i data-feather="{{ $meta['icon'] }}" class="w-5 h-5 mr-2 {{ $meta['text'] }}"></i>
                        {{ $item['total'] }} kunjungan {{ strtolower($meta['label']) }} bulan ini, {{ $item['persen'] }}% dari total kunjungan.
                    </div>
                    @include('pages.dashboard._kunjungan-list', ['items' => $statusItems, 'empty' => 'Tidak ada kunjungan dengan status ini.'])
                </div>
            </template>
        @endforeach

        <template id="insight-prioritas">
            <div>
                <div class="alert alert-outline-danger show flex items-center mb-5" role="alert">
                    <i data-feather="activity" class="w-5 h-5 mr-2"></i>
                    {{ $kasusPrioritas }} kasus berat atau dirujuk bulan ini perlu dipantau sampai tuntas.
                </div>
                <div class="divide-y divide-slate-200/60 dark:divide-darkmode-400">
                    @forelse ($kunjungan_prioritas_list as $kunjungan)
                        <div class="py-3 flex items-center">
                            <div class="w-9 h-9 rounded-full bg-danger/10 flex items-center justify-center">
                                <i data-feather="activity" class="w-4 h-4 text-danger"></i>
                            </div>
                            <div class="ml-3 min-w-0">
                                <div class="font-medium truncate">{{ optional($kunjungan->anggota)->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $kunjungan->tanggal->format('d/m/Y') }} - {{ $kunjungan->keluhan }}</div>
                            </div>
                            <span class="ml-auto px-2 py-0.5 rounded-full text-xs bg-danger/10 text-danger capitalize">{{ $kunjungan->status }}</span>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-6">Tidak ada kasus prioritas bulan ini.</div>
                    @endforelse
                </div>
            </div>
        </template>

        @foreach ($tipe_anggota_stat as $item)
            @php $anggotaTipeItems = $anggota_tipe_detail->get($item['tipe'], collect()); @endphp
            <template id="insight-anggota-tipe-{{ $item['tipe'] }}">
                <div>
                    <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                        <i data-feather="users" class="w-5 h-5 mr-2"></i>
                        {{ $item['total'] }} anggota kategori {{ strtolower($item['label']) }} aktif.
                    </div>
                    @include('pages.dashboard._anggota-list', ['items' => $anggotaTipeItems, 'empty' => 'Tidak ada anggota pada kategori ini.'])
                </div>
            </template>
        @endforeach

        <template id="insight-anggota-petugas">
            <div>
                <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                    <i data-feather="user-check" class="w-5 h-5 mr-2"></i>
                    {{ $total_guru }} guru dan staff aktif sebagai pendukung layanan UKS.
                </div>
                @include('pages.dashboard._anggota-list', ['items' => $anggota_petugas_detail, 'empty' => 'Belum ada data guru atau staff.'])
            </div>
        </template>

        @foreach ($jenjang_stat as $item)
            @php $anggotaJenjangItems = $anggota_jenjang_detail->get($item->id, collect()); @endphp
            <template id="insight-anggota-jenjang-{{ $item->id }}">
                <div>
                    <div class="alert alert-outline-primary show flex items-center mb-4" role="alert">
                        <i data-feather="layers" class="w-5 h-5 mr-2"></i>
                        {{ $item->total_aktif }} anggota aktif di jenjang {{ $item->nama }}, termasuk {{ $item->total_siswa }} siswa.
                    </div>
                    @include('pages.dashboard._anggota-list', ['items' => $anggotaJenjangItems, 'empty' => 'Tidak ada anggota pada jenjang ini.'])
                </div>
            </template>
        @endforeach
    </div>

    {{-- ============================================ --}}
    {{-- MODAL TAMBAH --}}
    {{-- ============================================ --}}
    <div id="modal-tambah" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('dashboard.kunjungan.store') }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="dashboard">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Tambah Kunjungan UKS</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Nama Anggota <span class="text-danger">*</span></label>
                            <select name="anggota_id" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach ($anggota_aktif as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ ucfirst(str_replace('_', ' ', $a->tipe)) }} - {{ optional($a->jenjang)->nama }})</option>
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
                    <input type="hidden" name="redirect_to" value="dashboard">
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
                    <input type="hidden" name="redirect_to" value="dashboard">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Tambah Pemeriksaan</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="tambah-anggota_id" class="form-label">Anggota <span class="text-danger">*</span></label>
                            <select id="tambah-anggota_id" name="anggota_id" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach ($pemeriksaan_anggota as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ ucfirst($a->tipe) }} - {{ optional($a->jenjang)->nama }})</option>
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
                    <input type="hidden" name="redirect_to" value="dashboard">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Kunjungan UKS</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 p-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="form-label">Nama Anggota <span class="text-danger">*</span></label>
                            <select name="anggota_id" id="edit-anggota_id" class="form-select" required>
                                <option value="">-- Pilih Anggota --</option>
                                @foreach ($anggota_aktif as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ ucfirst(str_replace('_', ' ', $a->tipe)) }} - {{ optional($a->jenjang)->nama }})</option>
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

    document.querySelectorAll('[data-insight-template]').forEach(trigger => {
        trigger.addEventListener('click', function () {
            const template = document.getElementById(this.dataset.insightTemplate)
            const title = document.getElementById('dashboard-insight-title')
            const body = document.getElementById('dashboard-insight-body')

            if (!template || !title || !body) return

            title.textContent = this.dataset.insightTitle || 'Ringkasan Dashboard'
            body.innerHTML = template.innerHTML

            if (window.feather) {
                window.feather.replace()
            }
        })
    })
</script>
@endsection
