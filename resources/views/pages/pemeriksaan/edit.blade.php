@extends('../layout/' . $layout)

@section('subhead')
    <title>Edit Pemeriksaan - UKS Sekolah</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Edit Pemeriksaan</h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-10">
            <div class="intro-y box p-5">
                @if ($errors->any())
                    <div class="alert alert-danger show mb-5">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pemeriksaan.update', $pemeriksaan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('pages.pemeriksaan._form')
                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('pemeriksaan.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
