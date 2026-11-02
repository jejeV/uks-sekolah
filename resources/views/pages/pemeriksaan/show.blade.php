@extends('../layout/' . $layout)

@section('subhead')
    <title>Detail Raport Kesehatan - UKS Sekolah</title>
@endsection

@section('subcontent')
    @php
        $anggota = $pemeriksaan->anggota;
        $isGuru = $anggota->tipe === 'guru';
        $perluTindakLanjut = in_array($pemeriksaan->pendengaran, ['kurang', 'tuli'], true)
            || in_array($pemeriksaan->kondisi_gigi, ['caries', 'perlu_perawatan'], true);
        $items = [
            ['Berat Badan', $pemeriksaan->berat_badan ? $pemeriksaan->berat_badan . ' kg' : '-'],
            ['Tinggi Badan', $pemeriksaan->tinggi_badan ? $pemeriksaan->tinggi_badan . ' cm' : '-'],
            ['BMI', $pemeriksaan->bmi ?? '-'],
            ['Penglihatan (Kiri / Kanan)', ($pemeriksaan->penglihatan_kiri ?: '-') . ' / ' . ($pemeriksaan->penglihatan_kanan ?: '-')],
            ['Pendengaran', $pemeriksaan->pendengaran ? ucfirst($pemeriksaan->pendengaran) : '-'],
            ['Kondisi Gigi', $pemeriksaan->kondisi_gigi ? ucfirst(str_replace('_', ' ', $pemeriksaan->kondisi_gigi)) : '-'],
        ];
        if ($isGuru) {
            $items[] = ['Gula Darah', $pemeriksaan->gula_darah ? $pemeriksaan->gula_darah . ' mg/dL' : '-'];
            $items[] = ['Kolesterol', $pemeriksaan->kolesterol ? $pemeriksaan->kolesterol . ' mg/dL' : '-'];
        }
    @endphp

    <div class="intro-y flex flex-col sm:flex-row sm:items-center gap-3 mt-8">
        <h2 class="text-lg font-medium mr-auto">Detail Raport Kesehatan</h2>
        <div class="flex flex-wrap gap-2">
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
            </a>
            <a href="{{ route('pemeriksaan.edit', $pemeriksaan) }}" class="btn btn-outline-primary">
                <i data-feather="edit-2" class="w-4 h-4 mr-2"></i> Ubah
            </a>
            <a href="{{ route('pemeriksaan.raport', $pemeriksaan) }}" class="btn btn-primary" data-download>
                <i data-feather="download" class="w-4 h-4 mr-2"></i> Raport PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-4">
            <div class="box p-5">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center flex-none">
                        <i data-feather="user" class="w-6 h-6 text-primary"></i>
                    </div>
                    <div class="ml-4 min-w-0">
                        <a href="{{ route('anggota.show', $anggota) }}" class="font-medium text-primary block truncate">{{ $anggota->nama }}</a>
                        <div class="text-slate-500 text-sm">{{ $anggota->nis_nip }} · {{ $anggota->tipe_label }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400 text-sm">
                    <div>
                        <div class="text-slate-500">Jenjang / Kelas</div>
                        <div class="font-medium mt-1">{{ optional($anggota->jenjang)->nama ?? '-' }}{{ $anggota->kelas ? ' / ' . $anggota->kelas : '' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Periode</div>
                        <div class="font-medium mt-1">Smt {{ $pemeriksaan->semester }} · {{ $pemeriksaan->tahun_ajaran }}/{{ $pemeriksaan->tahun_ajaran + 1 }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Petugas</div>
                        <div class="font-medium mt-1">{{ optional($pemeriksaan->petugas)->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Dicatat</div>
                        <div class="font-medium mt-1">{{ optional($pemeriksaan->created_at)->format('d/m/Y') ?? '-' }}</div>
                    </div>
                </div>
                @if ($perluTindakLanjut)
                    <div class="alert alert-warning-soft show flex items-center text-sm mt-5" role="alert">
                        <i data-feather="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i>
                        Perlu tindak lanjut (pendengaran/gigi).
                    </div>
                @endif
            </div>
        </div>

        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($items as [$label, $nilai])
                        <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4">
                            <div class="text-slate-500 text-xs">{{ $label }}</div>
                            <div class="font-medium text-base mt-1">{{ $nilai }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5">
                    <div class="text-slate-500 text-xs">Catatan</div>
                    <div class="mt-1">{{ $pemeriksaan->catatan ?: 'Tidak ada catatan khusus.' }}</div>
                </div>
            </div>

            <div class="flex justify-end mt-5">
                <form action="{{ route('pemeriksaan.destroy', $pemeriksaan) }}" method="POST"
                      data-confirm="Hapus raport kesehatan {{ $anggota->nama }} semester {{ $pemeriksaan->semester }}? Data tidak dapat dikembalikan.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i data-feather="trash-2" class="w-4 h-4 mr-2"></i> Hapus Raport
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
