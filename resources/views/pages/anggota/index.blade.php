@extends('../layout/' . $layout)

@section('subhead')
    <title>Data Anggota - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Data Anggota</h2>
        <a href="{{ route('anggota.create') }}" class="btn btn-primary mt-3 sm:mt-0">Tambah Anggota</a>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            @if (session('success'))
                <div class="alert alert-success show flex items-center mb-5" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="box p-5">
                <form method="GET" action="{{ route('anggota.index') }}" class="grid grid-cols-12 gap-4 mb-5">
                    <div class="col-span-12 xl:col-span-4">
                        <label for="search" class="form-label">Cari</label>
                        <input id="search" name="search" value="{{ request('search') }}" type="text" class="form-control" placeholder="Cari nama atau NIS/NIP">
                    </div>
                    <div class="col-span-6 xl:col-span-3">
                        <label for="tipe" class="form-label">Tipe</label>
                        <select id="tipe" name="tipe" class="form-select">
                            <option value="">Semua</option>
                            <option value="siswa" {{ request('tipe') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ request('tipe') === 'guru' ? 'selected' : '' }}>Guru</option>
                            <option value="tenaga_kependidikan" {{ request('tipe') === 'tenaga_kependidikan' ? 'selected' : '' }}>Tenaga Kependidikan</option>
                        </select>
                    </div>
                    <div class="col-span-6 xl:col-span-3">
                        <label for="jenjang_id" class="form-label">Jenjang</label>
                        <select id="jenjang_id" name="jenjang_id" class="form-select">
                            <option value="">Semua</option>
                            @foreach ($jenjang as $item)
                                <option value="{{ $item->id }}" {{ request('jenjang_id') == $item->id ? 'selected' : '' }}>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 xl:col-span-2 flex items-end">
                        <button type="submit" class="btn btn-primary w-full">Filter</button>
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
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->jenjang?->nama ?? '-' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $item->tipe)) }}</td>
                                    <td>{{ $item->kelas ?: '-' }}</td>
                                    <td>{{ optional($item->tgl_lahir)->format('d-m-Y') ?: '-' }}</td>
                                    <td>{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td>
                                    <td class="table-report__action w-56">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('anggota.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('anggota.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus anggota ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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
