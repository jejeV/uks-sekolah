@extends('../layout/' . $layout)

@section('subhead')
    <title>Ubah Data {{ $anggota->nama }} - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Ubah Data {{ $anggota->tipe_label }}</h2>
        <a href="{{ route('anggota.show', $anggota) }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <form action="{{ route('anggota.update', $anggota) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('pages.anggota._form', ['anggota' => $anggota])
                    <div class="mt-5 flex items-center gap-3 border-t border-slate-200/60 dark:border-darkmode-400 pt-5">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" class="w-4 h-4 mr-2"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('anggota.show', $anggota) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
