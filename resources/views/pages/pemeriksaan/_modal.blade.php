{{--
    Modal input raport kesehatan.
    Parameter: $redirectTo, $pickerTipe (siswa|pemeriksaan), $selectedAnggota + $fixedAnggota (opsional)
--}}
@php
    $modalId = 'modal-tambah-pemeriksaan';
    $fixedAnggota = $fixedAnggota ?? false;
    $hasModalErrors = old('_modal') === $modalId && $errors->any();

    // Mode input beruntun: setelah "Simpan & Berikutnya", anggota berikutnya langsung terpilih.
    $lanjutAnggota = !$fixedAnggota && session('lanjut_anggota_id')
        ? \App\Models\Anggota::with('jenjang')->find(session('lanjut_anggota_id'))
        : null;
    $selectedAnggota = $lanjutAnggota ?? ($selectedAnggota ?? null);
@endphp

<div id="{{ $modalId }}" class="modal" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $modalId }}-title" data-tw-backdrop="static"
     @if ($hasModalErrors || $lanjutAnggota) data-open-on-load @endif>
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
                    @elseif ($lanjutAnggota)
                        <div class="alert alert-success-soft show flex items-center text-sm mb-4" role="status">
                            <i data-feather="fast-forward" class="w-4 h-4 mr-2 flex-none"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @include('pages.pemeriksaan._form', [
                        'modalId'         => $modalId,
                        'pickerTipe'      => $pickerTipe ?? 'pemeriksaan',
                        'selectedAnggota' => $selectedAnggota,
                        'fixedAnggota'    => $fixedAnggota,
                    ])
                </div>
                <div class="modal-footer flex flex-wrap justify-end gap-2">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24">Batal</button>
                    @unless ($fixedAnggota)
                        <button type="submit" name="lanjut" value="1" class="btn btn-outline-primary tooltip"
                                title="Simpan, lalu langsung isi raport siswa berikutnya di kelas yang sama">
                            <i data-feather="fast-forward" class="w-4 h-4 mr-2"></i> Simpan &amp; Berikutnya
                        </button>
                    @endunless
                    <button type="submit" class="btn btn-primary w-28">
                        <i data-feather="save" class="w-4 h-4 mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
