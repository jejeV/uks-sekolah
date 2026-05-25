@extends('../layout/' . $layout)

@section('subhead')
    <title>Data Pemeriksaan - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Data Pemeriksaan Kesehatan</h2>
        <button data-tw-toggle="modal" data-tw-target="#modal-tambah-pemeriksaan" class="btn btn-primary mt-3 sm:mt-0">Tambah Pemeriksaan</button>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            @if (session('success'))
                <div class="alert alert-success show flex items-center mb-5" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="box p-5">
                <form action="{{ route('pemeriksaan.index') }}" method="GET" class="grid grid-cols-12 gap-4 mb-5">
                    <div class="col-span-12 sm:col-span-4">
                        <label for="search" class="form-label">Cari Anggota</label>
                        <input id="search" name="search" value="{{ request('search') }}" type="text" class="form-control" placeholder="Masukkan nama...">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="semester" class="form-label">Semester</label>
                        <select id="semester" name="semester" class="form-select">
                            <option value="">Semua</option>
                            <option value="1" {{ request('semester') === '1' ? 'selected' : '' }}>1</option>
                            <option value="2" {{ request('semester') === '2' ? 'selected' : '' }}>2</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                        <select id="tahun_ajaran" name="tahun_ajaran" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($tahunOptions as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun_ajaran') == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-1 flex items-end">
                        <button type="submit" class="btn btn-primary w-full">Filter</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="table table-report -mt-2">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Anggota</th>
                                <th class="whitespace-nowrap">Semester</th>
                                <th class="whitespace-nowrap">Tahun</th>
                                <th class="whitespace-nowrap">BMI</th>
                                <th class="whitespace-nowrap">Pendengaran</th>
                                <th class="whitespace-nowrap">Gigi</th>
                                <th class="whitespace-nowrap">Petugas</th>
                                <th class="text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pemeriksaan as $item)
                                <tr class="intro-x">
                                    <td>
                                        <div class="font-medium whitespace-nowrap">{{ $item->anggota->nama }}</div>
                                        <div class="text-slate-500 text-xs mt-0.5">{{ ucfirst(str_replace('_', ' ', $item->anggota->tipe)) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap">{{ $item->semester }}</td>
                                    <td class="whitespace-nowrap">{{ $item->tahun_ajaran }}</td>
                                    <td class="whitespace-nowrap">{{ $item->bmi ?? '-' }}</td>
                                    <td class="whitespace-nowrap">{{ $item->pendengaran ? ucfirst($item->pendengaran) : '-' }}</td>
                                    <td class="whitespace-nowrap">{{ $item->kondisi_gigi ? str_replace('_', ' ', ucfirst($item->kondisi_gigi)) : '-' }}</td>
                                    <td class="whitespace-nowrap">{{ $item->petugas?->name ?? '-' }}</td>
                                    <td class="table-report__action w-40">
                                        <div class="flex justify-center items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-primary"
                                                data-tw-toggle="modal"
                                                data-tw-target="#modal-edit-pemeriksaan"
                                                data-action="{{ route('pemeriksaan.update', $item) }}"
                                                data-anggota_id="{{ $item->anggota_id }}"
                                                data-semester="{{ $item->semester }}"
                                                data-tahun_ajaran="{{ $item->tahun_ajaran }}"
                                                data-berat_badan="{{ $item->berat_badan }}"
                                                data-tinggi_badan="{{ $item->tinggi_badan }}"
                                                data-penglihatan_kiri="{{ $item->penglihatan_kiri }}"
                                                data-penglihatan_kanan="{{ $item->penglihatan_kanan }}"
                                                data-pendengaran="{{ $item->pendengaran }}"
                                                data-kondisi_gigi="{{ $item->kondisi_gigi }}"
                                                data-catatan="{{ $item->catatan }}"
                                                onclick="bukaEditPemeriksaan(this)">
                                                Edit
                                            </button>
                                            <form action="{{ route('pemeriksaan.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500 py-6">Belum ada data pemeriksaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mt-5">
                    <div class="text-slate-500">Menampilkan {{ $pemeriksaan->firstItem() ?: 0 }} sampai {{ $pemeriksaan->lastItem() ?: 0 }} dari {{ $pemeriksaan->total() }} data</div>
                    <div>{{ $pemeriksaan->withQueryString()->links() }}</div>
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

    <div id="modal-edit-pemeriksaan" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="form-edit-pemeriksaan" action="#" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Ubah Pemeriksaan</h2>
                    </div>
                    <div class="modal-body p-5">
                        @include('pages.pemeriksaan._form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaEditPemeriksaan(element) {
            const form = document.getElementById('form-edit-pemeriksaan');
            form.action = element.dataset.action;
            form.querySelector('[name="anggota_id"]').value = element.dataset.anggota_id;
            form.querySelector('[name="semester"]').value = element.dataset.semester;
            form.querySelector('[name="tahun_ajaran"]').value = element.dataset.tahun_ajaran;
            form.querySelector('[name="berat_badan"]').value = element.dataset.berat_badan;
            form.querySelector('[name="tinggi_badan"]').value = element.dataset.tinggi_badan;
            form.querySelector('[name="penglihatan_kiri"]').value = element.dataset.penglihatan_kiri;
            form.querySelector('[name="penglihatan_kanan"]').value = element.dataset.penglihatan_kanan;
            form.querySelector('[name="pendengaran"]').value = element.dataset.pendengaran;
            form.querySelector('[name="kondisi_gigi"]').value = element.dataset.kondisi_gigi;
            form.querySelector('[name="catatan"]').value = element.dataset.catatan;
        }
    </script>
@endsection
