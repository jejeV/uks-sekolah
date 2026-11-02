@extends('../layout/' . $layout)

@section('subhead')
    <title>Tambah Siswa / Guru - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Tambah Siswa / Guru</h2>
        <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <form action="{{ route('anggota.store') }}" method="POST">
                    @csrf
                    @include('pages.anggota._form')
                    <div class="mt-5 flex items-center gap-3 border-t border-slate-200/60 dark:border-darkmode-400 pt-5">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" class="w-4 h-4 mr-2"></i> Simpan
                        </button>
                        <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
