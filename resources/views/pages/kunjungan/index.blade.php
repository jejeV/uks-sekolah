@extends('../layout/' . $layout)

@section('subhead')
    <title>Data Kunjungan - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $statusMeta = [
            'ringan' => ['label' => 'Ringan', 'class' => 'text-success bg-success/10'],
            'sedang' => ['label' => 'Sedang', 'class' => 'text-warning bg-warning/10'],
            'berat' => ['label' => 'Berat', 'class' => 'text-danger bg-danger/10'],
            'dirujuk' => ['label' => 'Dirujuk', 'class' => 'text-pending bg-pending/10'],
        ];
        $activeFilters = array_filter(request()->only(['search', 'tanggal', 'status', 'prioritas', 'jenjang_id', 'kelas']));
    @endphp

    <div id="kunjungan-loading" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/35">
        <div class="box px-5 py-4 flex items-center gap-3 shadow-lg">
            <i data-feather="loader" class="w-5 h-5 animate-spin text-primary"></i>
            <span class="font-medium">Memuat data...</span>
        </div>
    </div>

    <div class="intro-y flex flex-col sm:flex-row sm:items-center mt-8 gap-3">
        <h2 class="text-lg font-medium mr-auto">Data Kunjungan UKS</h2>
        <a href="{{ route('dashboard') }}" data-loading-link class="btn btn-outline-secondary h-10 px-4 gap-2">
            <i data-feather="arrow-left" class="w-4 h-4"></i>
            Dashboard
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success show flex items-center mt-5" role="alert">
            <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($activeFilters)
        <div class="alert alert-primary-soft show flex flex-col sm:flex-row sm:items-center gap-3 mt-5" role="alert">
            <div class="flex items-center mr-auto">
                <i data-feather="filter" class="w-5 h-5 mr-2"></i>
                <span>Filter aktif untuk data kunjungan.</span>
            </div>
            <a href="{{ route('kunjungan.index') }}" data-loading-link class="btn btn-outline-primary h-9 px-3">Reset Filter</a>
        </div>
    @endif

    <div class="intro-y box p-5 mt-5">
        <form action="{{ route('kunjungan.index') }}" method="GET" data-loading-form class="grid grid-cols-12 gap-4 mb-5">
            <div class="col-span-12 xl:col-span-3">
                <label for="search" class="form-label">Cari Siswa/Guru</label>
                <input id="search" name="search" value="{{ request('search') }}" type="text" class="form-control" placeholder="Masukkan nama...">
            </div>
            <div class="col-span-6 xl:col-span-2">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input id="tanggal" name="tanggal" value="{{ request('tanggal') }}" type="date" class="form-control">
            </div>
            <div class="col-span-6 xl:col-span-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($statusMeta as $status => $meta)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 xl:col-span-2">
                <label for="jenjang_id" class="form-label">Jenjang</label>
                <select id="jenjang_id" name="jenjang_id" class="form-select">
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
            <div class="col-span-6 xl:col-span-1 flex items-end">
                <button type="submit" class="btn btn-primary w-10 h-10 p-0 tooltip" title="Cari data" aria-label="Cari data">
                    <i data-feather="search" class="w-4 h-4"></i>
                </button>
            </div>
            @if (request('prioritas') === 'tinggi')
                <input type="hidden" name="prioritas" value="tinggi">
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Siswa/Guru</th>
                        <th class="whitespace-nowrap">Tanggal</th>
                        <th class="whitespace-nowrap">Keluhan</th>
                        <th class="whitespace-nowrap">Tindakan</th>
                        <th class="whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kunjungan as $item)
                        @php $meta = $statusMeta[$item->status] ?? $statusMeta['ringan']; @endphp
                        <tr class="intro-x">
                            <td>
                                @if ($item->anggota)
                                    <a href="{{ route('anggota.show', $item->anggota) }}" data-loading-link class="font-medium text-primary whitespace-nowrap">{{ $item->anggota->nama }}</a>
                                    <div class="text-slate-500 text-xs mt-0.5">
                                        {{ ucfirst(str_replace('_', ' ', $item->anggota->tipe)) }} - {{ $item->anggota->jenjang?->nama ?? '-' }}{{ $item->anggota->kelas ? ' / ' . $item->anggota->kelas : '' }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $item->tanggal->format('d/m/Y') }}
                                <div class="text-slate-500 text-xs mt-0.5">{{ $item->jam ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $item->keluhan }}</div>
                                <div class="text-slate-500 text-xs mt-1">{{ $item->diagnosis ?: 'Belum ada diagnosis.' }}</div>
                            </td>
                            <td>{{ $item->tindakan ?: '-' }}</td>
                            <td class="whitespace-nowrap">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-500 py-8">Belum ada data kunjungan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-5">
            <div class="text-slate-500">Menampilkan {{ $kunjungan->firstItem() ?: 0 }} sampai {{ $kunjungan->lastItem() ?: 0 }} dari {{ $kunjungan->total() }} data</div>
            <div>{{ $kunjungan->withQueryString()->links() }}</div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function showKunjunganLoading() {
            const loading = document.getElementById('kunjungan-loading')
            if (loading) {
                loading.classList.remove('hidden')
                loading.classList.add('flex')
            }
        }

        document.querySelectorAll('[data-loading-link]').forEach(link => {
            link.addEventListener('click', event => {
                if (event.defaultPrevented || link.classList.contains('pointer-events-none')) return
                showKunjunganLoading()
            })
        })

        document.querySelectorAll('[data-loading-form]').forEach(form => {
            form.addEventListener('submit', () => {
                showKunjunganLoading()
                form.querySelectorAll('button[type="submit"]').forEach(button => {
                    button.disabled = true
                    button.classList.add('opacity-75')
                })
            })
        })
    </script>
@endsection
