@if (session('success'))
    <div class="alert alert-success-soft show flex items-center mt-5" role="alert">
        <i data-feather="check-circle" class="w-5 h-5 mr-2 flex-none"></i>
        <span class="mr-auto">{{ session('success') }}</span>
        <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Tutup">
            <i data-feather="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger-soft show flex items-start mt-5" role="alert">
        <i data-feather="alert-circle" class="w-5 h-5 mr-2 mt-0.5 flex-none"></i>
        <div class="mr-auto">
            <div class="font-medium">Data belum dapat disimpan. Periksa kembali isian berikut:</div>
            <ul class="list-disc pl-5 mt-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Tutup">
            <i data-feather="x" class="w-4 h-4"></i>
        </button>
    </div>
@endif

@if (session('import_errors'))
    <div class="alert alert-warning-soft show mt-5" role="alert">
        <div class="font-medium mb-1">Beberapa baris tidak diimport:</div>
        <ul class="list-disc pl-5 text-sm">
            @foreach (session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
