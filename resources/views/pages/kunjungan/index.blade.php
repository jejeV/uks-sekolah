@extends('../layout/' . $layout)

@section('subhead')
    <title>Kunjungan UKS - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $statusMeta = [
            'ringan'  => ['label' => 'Ringan', 'class' => 'text-success bg-success/10'],
            'sedang'  => ['label' => 'Sedang', 'class' => 'text-warning bg-warning/10'],
            'berat'   => ['label' => 'Berat', 'class' => 'text-danger bg-danger/10'],
            'dirujuk' => ['label' => 'Dirujuk', 'class' => 'text-pending bg-pending/10'],
        ];
        $activeFilters = array_filter(request()->only(['search', 'tanggal', 'status', 'prioritas', 'jenjang_id', 'kelas']));
        $chipQuery = fn (array $override) => array_filter(array_merge(request()->except(['page', 'prioritas']), $override));
    @endphp

    <div class="intro-y flex flex-col sm:flex-row sm:items-center mt-8 gap-3">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">Kunjungan UKS</h2>
            <div class="text-slate-500 text-sm mt-1">Riwayat siswa dan guru yang berkunjung ke UKS.</div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="dropdown">
                <button class="dropdown-toggle btn btn-outline-secondary h-10 px-4 gap-2" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-feather="download" class="w-4 h-4"></i> Export
                    <i data-feather="chevron-down" class="w-4 h-4"></i>
                </button>
                <div class="dropdown-menu w-48">
                    <ul class="dropdown-content">
                        <li><a href="{{ route('export.kunjungan', array_merge($activeFilters, ['format' => 'excel'])) }}" data-download class="dropdown-item"><i data-feather="file-text" class="w-4 h-4 mr-2"></i>Excel</a></li>
                        <li><a href="{{ route('export.kunjungan', array_merge($activeFilters, ['format' => 'pdf'])) }}" data-download class="dropdown-item"><i data-feather="file" class="w-4 h-4 mr-2"></i>PDF</a></li>
                    </ul>
                </div>
            </div>
            <button type="button" data-tw-toggle="modal" data-tw-target="#modal-tambah-kunjungan" class="btn btn-primary h-10 px-4 gap-2 shadow-md">
                <i data-feather="plus" class="w-4 h-4"></i> Catat Kunjungan
            </button>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <form action="{{ route('kunjungan.index') }}" method="GET" class="grid grid-cols-12 gap-3" role="search">
            <input type="hidden" name="status" value="{{ request('status') }}">
            @if (request('prioritas') === 'tinggi')
                <input type="hidden" name="prioritas" value="tinggi">
            @endif
            <div class="col-span-12 lg:col-span-5">
                <label for="search" class="sr-only">Cari nama atau NIS</label>
                <div class="relative">
                    <i data-feather="search" class="w-4 h-4 absolute inset-y-0 my-auto left-3 text-slate-400"></i>
                    <input id="search" name="search" value="{{ request('search') }}" type="search" class="form-control pl-9" placeholder="Cari nama atau NIS/NIP, tekan Enter">
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4 lg:col-span-3">
                <label for="tanggal" class="sr-only">Tanggal</label>
                <input id="tanggal" name="tanggal" value="{{ request('tanggal') }}" type="date" class="form-control" data-auto-submit aria-label="Filter tanggal">
            </div>
            <div class="col-span-6 sm:col-span-4 lg:col-span-2">
                <label for="jenjang_id" class="sr-only">Jenjang</label>
                <select id="jenjang_id" name="jenjang_id" class="form-select" data-auto-submit>
                    <option value="">Semua jenjang</option>
                    @foreach ($jenjang as $item)
                        <option value="{{ $item->id }}" @if (request('jenjang_id') == $item->id) selected @endif>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 sm:col-span-4 lg:col-span-2">
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
            <a href="{{ route('kunjungan.index', $chipQuery(['status' => null])) }}" class="filter-chip {{ !request('status') && request('prioritas') !== 'tinggi' ? 'filter-chip--active' : '' }}">Semua</a>
            @foreach ($statusMeta as $status => $meta)
                <a href="{{ route('kunjungan.index', $chipQuery(['status' => $status])) }}" class="filter-chip {{ request('status') === $status ? 'filter-chip--active' : '' }}">{{ $meta['label'] }}</a>
            @endforeach
            <a href="{{ route('kunjungan.index', array_merge($chipQuery(['status' => null]), ['prioritas' => 'tinggi'])) }}" class="filter-chip {{ request('prioritas') === 'tinggi' ? 'filter-chip--active' : '' }}">
                <i data-feather="alert-triangle" class="w-3 h-3"></i> Prioritas
            </a>
            <a href="{{ route('kunjungan.index', $chipQuery(['tanggal' => now()->toDateString()])) }}" class="filter-chip {{ request('tanggal') === now()->toDateString() ? 'filter-chip--active' : '' }}">Hari ini</a>
            @if ($activeFilters)
                <a href="{{ route('kunjungan.index') }}" class="ml-auto text-sm text-primary flex items-center">
                    <i data-feather="x" class="w-4 h-4 mr-1"></i> Reset filter
                </a>
            @endif
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Siswa / Guru</th>
                        <th class="whitespace-nowrap">Waktu</th>
                        <th class="whitespace-nowrap">Keluhan & Diagnosis</th>
                        <th class="whitespace-nowrap hidden lg:table-cell">Tindakan / Obat</th>
                        <th class="whitespace-nowrap">Status</th>
                        <th class="whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kunjungan as $item)
                        @php $meta = $statusMeta[$item->status] ?? $statusMeta['ringan']; @endphp
                        <tr class="intro-x">
                            <td>
                                @if ($item->anggota)
                                    <a href="{{ route('anggota.show', $item->anggota) }}" class="font-medium text-primary whitespace-nowrap">{{ $item->anggota->nama }}</a>
                                    <div class="text-slate-500 text-xs mt-0.5 whitespace-nowrap">
                                        {{ $item->anggota->tipe_label }} · {{ $item->anggota->jenjang?->nama ?? '-' }}{{ $item->anggota->kelas ? ' / ' . $item->anggota->kelas : '' }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $item->tanggal->translatedFormat('d M Y') }}
                                <div class="text-slate-500 text-xs mt-0.5">{{ $item->jam ? substr($item->jam, 0, 5) : '-' }} · {{ optional($item->petugas)->name ?? '-' }}</div>
                            </td>
                            <td class="max-w-xs">
                                <div class="font-medium">{{ $item->keluhan }}</div>
                                <div class="text-slate-500 text-xs mt-1">{{ $item->diagnosis ?: 'Belum ada diagnosis.' }}</div>
                            </td>
                            <td class="max-w-xs hidden lg:table-cell">
                                {{ $item->tindakan ?: '-' }}
                                @if ($item->obat)
                                    <div class="text-slate-500 text-xs mt-1">Obat: {{ $item->obat }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge-status {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary tooltip" title="Ubah" aria-label="Ubah kunjungan"
                                            data-tw-toggle="modal" data-tw-target="#modal-ubah-kunjungan"
                                            data-form-action="{{ route('kunjungan.update', $item) }}"
                                            data-fill-form="{{ json_encode([
                                                '_kunjungan_id' => $item->id,
                                                'anggota'       => $item->anggota ? ['id' => $item->anggota->id, 'tipe' => $item->anggota->tipe, 'label' => $item->anggota->picker_label] : null,
                                                'tanggal'       => $item->tanggal->toDateString(),
                                                'jam'           => $item->jam ? substr($item->jam, 0, 5) : '',
                                                'keluhan'       => $item->keluhan,
                                                'diagnosis'     => $item->diagnosis,
                                                'tindakan'      => $item->tindakan,
                                                'obat'          => $item->obat,
                                                'status'        => $item->status,
                                            ]) }}">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('kunjungan.destroy', $item) }}" method="POST"
                                          data-confirm="Hapus kunjungan {{ optional($item->anggota)->nama }} tanggal {{ $item->tanggal->format('d/m/Y') }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger tooltip" title="Hapus" aria-label="Hapus kunjungan">
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
                                {{ $activeFilters ? 'Tidak ada kunjungan yang cocok dengan filter.' : 'Belum ada data kunjungan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-5">
            <div class="text-slate-500 text-sm">Menampilkan {{ $kunjungan->firstItem() ?: 0 }}–{{ $kunjungan->lastItem() ?: 0 }} dari {{ $kunjungan->total() }} data</div>
            <div>{{ $kunjungan->links() }}</div>
        </div>
    </div>

    @include('pages.kunjungan._modal', ['redirectTo' => 'kunjungan.index'])
    @include('pages.kunjungan._modal', ['redirectTo' => 'kunjungan.index', 'mode' => 'edit'])
@endsection
