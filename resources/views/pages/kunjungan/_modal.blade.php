{{-- Modal tambah kunjungan. Parameter: $redirectTo (dashboard|kunjungan.index|anggota.show) --}}
@php
    $modalId = 'modal-tambah-kunjungan';
    $useOld = old('_modal') === $modalId;
    $value = fn ($key, $default = null) => $useOld ? old($key, $default) : $default;
@endphp

<div id="{{ $modalId }}" class="modal" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $modalId }}-title"
     @if ($useOld && $errors->any()) data-open-on-load @endif>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('kunjungan.store') }}">
                @csrf
                <input type="hidden" name="_modal" value="{{ $modalId }}">
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <div class="modal-header">
                    <h2 id="{{ $modalId }}-title" class="font-medium text-base mr-auto">Catat Kunjungan UKS</h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" data-tw-dismiss="modal" aria-label="Tutup">
                        <i data-feather="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 p-5">
                    @if ($useOld && $errors->any())
                        <div class="col-span-12 alert alert-danger-soft show text-sm" role="alert">{{ $errors->first() }}</div>
                    @endif
                    <div class="col-span-12 sm:col-span-6">
                        @include('components.anggota-picker', ['pickerId' => $modalId . '-anggota', 'tipe' => null, 'useOld' => $useOld])
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="{{ $modalId }}-tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input id="{{ $modalId }}-tanggal" type="date" name="tanggal" class="form-control" value="{{ $value('tanggal', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label for="{{ $modalId }}-jam" class="form-label">Jam</label>
                        <input id="{{ $modalId }}-jam" type="time" name="jam" class="form-control" value="{{ $value('jam', now()->format('H:i')) }}">
                    </div>
                    <div class="col-span-12">
                        <label for="{{ $modalId }}-keluhan" class="form-label">Keluhan <span class="text-danger">*</span></label>
                        <textarea id="{{ $modalId }}-keluhan" name="keluhan" class="form-control" rows="2" placeholder="Contoh: pusing, demam sejak pagi" required>{{ $value('keluhan') }}</textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="{{ $modalId }}-diagnosis" class="form-label">Diagnosis</label>
                        <textarea id="{{ $modalId }}-diagnosis" name="diagnosis" class="form-control" rows="2">{{ $value('diagnosis') }}</textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="{{ $modalId }}-tindakan" class="form-label">Tindakan</label>
                        <textarea id="{{ $modalId }}-tindakan" name="tindakan" class="form-control" rows="2" placeholder="Contoh: istirahat di UKS, kompres">{{ $value('tindakan') }}</textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label for="{{ $modalId }}-obat" class="form-label">Obat</label>
                        <input id="{{ $modalId }}-obat" type="text" name="obat" class="form-control" maxlength="200" value="{{ $value('obat') }}" placeholder="Contoh: Paracetamol 500mg">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <span class="form-label block">Tingkat Keluhan <span class="text-danger">*</span></span>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ([
                                'ringan'  => 'peer-checked:border-success peer-checked:bg-success/10 peer-checked:text-success',
                                'sedang'  => 'peer-checked:border-warning peer-checked:bg-warning/10 peer-checked:text-warning',
                                'berat'   => 'peer-checked:border-danger peer-checked:bg-danger/10 peer-checked:text-danger',
                                'dirujuk' => 'peer-checked:border-pending peer-checked:bg-pending/10 peer-checked:text-pending',
                            ] as $status => $checkedClass)
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $status }}" class="peer sr-only" @if ($value('status', 'ringan') === $status) checked @endif required>
                                    <span class="block text-center text-xs font-medium py-2 rounded-md border border-slate-200 dark:border-darkmode-400 {{ $checkedClass }} peer-focus-visible:ring-2 peer-focus-visible:ring-primary/40">
                                        {{ ucfirst($status) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <details class="col-span-12 group" @if ($value('nama_penyakit')) open @endif>
                        <summary class="cursor-pointer text-sm text-primary font-medium select-none">+ Catat sebagai riwayat penyakit (opsional)</summary>
                        <div class="grid grid-cols-12 gap-4 mt-3">
                            <div class="col-span-12 sm:col-span-8">
                                <label for="{{ $modalId }}-penyakit" class="form-label">Nama Penyakit</label>
                                <input id="{{ $modalId }}-penyakit" type="text" name="nama_penyakit" class="form-control" maxlength="100" value="{{ $value('nama_penyakit') }}" placeholder="Contoh: Asma">
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="{{ $modalId }}-icd" class="form-label">Kode ICD-10</label>
                                <input id="{{ $modalId }}-icd" type="text" name="kode_icd" class="form-control" maxlength="10" value="{{ $value('kode_icd') }}" placeholder="J45">
                            </div>
                        </div>
                    </details>
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
