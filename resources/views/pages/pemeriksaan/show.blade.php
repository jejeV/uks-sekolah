@extends('../layout/' . $layout)

@section('subhead')
    <title>Detail Pemeriksaan - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Detail Pemeriksaan</h2>
        <a href="{{ route('pemeriksaan.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <div class="box p-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-6">
                        <div class="font-medium">Anggota</div>
                        <div class="text-slate-600">{{ $pemeriksaan->anggota->nama }} ({{ ucfirst($pemeriksaan->anggota->tipe) }})</div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <div class="font-medium">Semester</div>
                        <div class="text-slate-600">{{ $pemeriksaan->semester }}</div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <div class="font-medium">Tahun Ajaran</div>
                        <div class="text-slate-600">{{ $pemeriksaan->tahun_ajaran }}</div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <div class="font-medium">Berat Badan</div>
                        <div class="text-slate-600">{{ $pemeriksaan->berat_badan ?? '-' }} kg</div>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <div class="font-medium">Tinggi Badan</div>
                        <div class="text-slate-600">{{ $pemeriksaan->tinggi_badan ?? '-' }} cm</div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="font-medium">BMI</div>
                        <div class="text-slate-600">{{ $pemeriksaan->bmi ?? '-' }}</div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="font-medium">Pendengaran</div>
                        <div class="text-slate-600">{{ $pemeriksaan->pendengaran ? ucfirst($pemeriksaan->pendengaran) : '-' }}</div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="font-medium">Kondisi Gigi</div>
                        <div class="text-slate-600">{{ $pemeriksaan->kondisi_gigi ? str_replace('_', ' ', ucfirst($pemeriksaan->kondisi_gigi)) : '-' }}</div>
                    </div>
                    <div class="col-span-12">
                        <div class="font-medium">Catatan</div>
                        <div class="text-slate-600">{{ $pemeriksaan->catatan ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
