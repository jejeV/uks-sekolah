{{--
    Modal input raport kesehatan.
    Parameter: $redirectTo, $pickerTipe (siswa|pemeriksaan), $selectedAnggota + $fixedAnggota (opsional)
--}}
@php
    $modalId = 'modal-tambah-pemeriksaan';
    $hasModalErrors = old('_modal') === $modalId && $errors->any();
@endphp

<div id="{{ $modalId }}" class="modal" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $modalId }}-title"
     @if ($hasModalErrors) data-open-on-load @endif>
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="{{ route('pemeriksaan.store') }}">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <div class="modal-header">
                    <h2 id="{{ $modalId }}-title" class="font-medium text-base mr-auto">Input Raport Kesehatan</h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" data-tw-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="modal-body p-5">
                    @if ($hasModalErrors)
                        <div class="alert alert-danger-soft show text-sm mb-4" role="alert">{{ $errors->first() }}</div>
                    @endif
                    @include('pages.pemeriksaan._form', [
                        'modalId'         => $modalId,
                        'pickerTipe'      => $pickerTipe ?? 'pemeriksaan',
                        'selectedAnggota' => $selectedAnggota ?? null,
                        'fixedAnggota'    => $fixedAnggota ?? false,
                    ])
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                    <button type="submit" class="btn btn-primary w-28">
                        <i data-feather="save" class="w-4 h-4 mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
