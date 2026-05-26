@extends('../layout/' . $layout)

@section('subhead')
    <title>Profil Kesehatan - {{ $anggota->nama }}</title>
@endsection

@section('subcontent')
    @php
        $jenisKelamin = $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
        $umur = $anggota->tgl_lahir ? $anggota->tgl_lahir->age . ' tahun' : '-';
        $mcuSelesai = (bool) $mcuSemesterIni;
        $statusMcuText = $mcuSelesai ? 'MCU semester ini sudah lengkap' : 'MCU semester ini belum dicatat';
        $statusMcuClass = $mcuSelesai ? 'text-success' : 'text-warning';
        $statusMcuBg = $mcuSelesai ? 'bg-success/10' : 'bg-warning/10';
    @endphp

    <div class="intro-y flex flex-col sm:flex-row sm:items-center mt-8 gap-3">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">Profil Kesehatan Siswa</h2>
            <div class="text-slate-500 mt-1">Rekam kesehatan, riwayat penyakit, dan hasil MCU per semester.</div>
        </div>
        <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </a>
        @if ($mcuTerakhir)
            <a href="{{ route('pemeriksaan.raport', $mcuTerakhir) }}" class="btn btn-primary">
                <i data-feather="file-text" class="w-4 h-4 mr-2"></i> Raport MCU PDF
            </a>
        @endif
    </div>

    <div class="intro-y box px-5 pt-5 mt-5">
        <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
            <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 rounded-full {{ $statusMcuBg }} flex items-center justify-center">
                    <i data-feather="user" class="w-10 h-10 {{ $statusMcuClass }}"></i>
                </div>
                <div class="ml-5 min-w-0">
                    <div class="font-medium text-lg truncate sm:whitespace-normal">{{ $anggota->nama }}</div>
                    <div class="text-slate-500">{{ $anggota->nis_nip }} - {{ $anggota->jenjang?->nama ?? '-' }} {{ $anggota->kelas ?: '' }}</div>
                    <div class="mt-2 inline-flex items-center px-2 py-1 rounded {{ $statusMcuBg }} {{ $statusMcuClass }} text-xs font-medium">
                        <i data-feather="{{ $mcuSelesai ? 'check-circle' : 'clock' }}" class="w-3.5 h-3.5 mr-1"></i>
                        {{ $statusMcuText }}
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-3">Identitas Siswa</div>
                <div class="grid grid-cols-2 gap-3 mt-4 text-sm">
                    <div>
                        <div class="text-slate-500">Jenis Kelamin</div>
                        <div class="font-medium mt-1">{{ $jenisKelamin }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Usia</div>
                        <div class="font-medium mt-1">{{ $umur }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Tipe</div>
                        <div class="font-medium mt-1">{{ ucfirst(str_replace('_', ' ', $anggota->tipe)) }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500">Status</div>
                        <div class="font-medium mt-1">{{ $anggota->aktif ? 'Aktif' : 'Tidak aktif' }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-3">Ringkasan UKS</div>
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <div class="text-center rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                        <div class="text-2xl font-medium">{{ $anggota->pemeriksaan->count() }}</div>
                        <div class="text-slate-500 text-xs mt-1">MCU</div>
                    </div>
                    <div class="text-center rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                        <div class="text-2xl font-medium">{{ $anggota->kunjungan->count() }}</div>
                        <div class="text-slate-500 text-xs mt-1">Kunjungan</div>
                    </div>
                    <div class="text-center rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                        <div class="text-2xl font-medium">{{ $riwayatAktif }}</div>
                        <div class="text-slate-500 text-xs mt-1">Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
            <li id="ringkasan-tab" class="nav-item" role="presentation">
                <a href="javascript:;" class="nav-link py-4 active" data-tw-toggle="tab" data-tw-target="#ringkasan" aria-controls="ringkasan" aria-selected="true" role="tab">Ringkasan</a>
            </li>
            <li id="mcu-tab" class="nav-item" role="presentation">
                <a href="javascript:;" class="nav-link py-4" data-tw-toggle="tab" data-tw-target="#mcu" aria-controls="mcu" aria-selected="false" role="tab">Hasil MCU</a>
            </li>
            <li id="riwayat-tab" class="nav-item" role="presentation">
                <a href="javascript:;" class="nav-link py-4" data-tw-toggle="tab" data-tw-target="#riwayat" aria-controls="riwayat" aria-selected="false" role="tab">Riwayat Penyakit</a>
            </li>
        </ul>
    </div>

    <div class="intro-y tab-content mt-5">
        <div id="ringkasan" class="tab-pane active" role="tabpanel" aria-labelledby="ringkasan-tab">
            <div class="grid grid-cols-12 gap-6">
                <div class="intro-y box col-span-12 lg:col-span-5">
                    <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                        <h2 class="font-medium text-base mr-auto">Status MCU Semester Ini</h2>
                        <span class="px-2 py-1 rounded text-xs font-medium {{ $statusMcuBg }} {{ $statusMcuClass }}">Semester {{ $semesterBerjalan }}</span>
                    </div>
                    <div class="p-5">
                        @if ($mcuSemesterIni)
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-slate-500">Berat / Tinggi</div>
                                    <div class="font-medium mt-1">{{ $mcuSemesterIni->berat_badan ?? '-' }} kg / {{ $mcuSemesterIni->tinggi_badan ?? '-' }} cm</div>
                                </div>
                                <div>
                                    <div class="text-slate-500">BMI</div>
                                    <div class="font-medium mt-1">{{ $mcuSemesterIni->bmi ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-500">Pendengaran</div>
                                    <div class="font-medium mt-1">{{ $mcuSemesterIni->pendengaran ? ucfirst($mcuSemesterIni->pendengaran) : '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-slate-500">Kondisi Gigi</div>
                                    <div class="font-medium mt-1">{{ $mcuSemesterIni->kondisi_gigi ? ucfirst(str_replace('_', ' ', $mcuSemesterIni->kondisi_gigi)) : '-' }}</div>
                                </div>
                            </div>
                            <div class="mt-5 text-slate-600">{{ $mcuSemesterIni->catatan ?: 'Tidak ada catatan khusus.' }}</div>
                            <div class="mt-5 flex gap-2">
                                <a href="{{ route('pemeriksaan.show', ['pemeriksaan' => $mcuSemesterIni, 'back' => 'profile']) }}" class="btn btn-outline-secondary">
                                    <i data-feather="eye" class="w-4 h-4 mr-2"></i> Detail
                                </a>
                                <a href="{{ route('pemeriksaan.raport', $mcuSemesterIni) }}" class="btn btn-primary">
                                    <i data-feather="download" class="w-4 h-4 mr-2"></i> Raport Orang Tua
                                </a>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i data-feather="clipboard" class="w-12 h-12 text-warning mx-auto"></i>
                                <div class="font-medium mt-4">MCU semester ini belum tersedia</div>
                                <div class="text-slate-500 mt-1">Petugas UKS perlu menginput pemeriksaan semester {{ $semesterBerjalan }} tahun ajaran {{ $tahunAjaran }}.</div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#modal-mcu-siswa" class="btn btn-primary mt-5">Input MCU</button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="intro-y box col-span-12 lg:col-span-7">
                    <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                        <h2 class="font-medium text-base mr-auto">Kunjungan UKS Terbaru</h2>
                    </div>
                    <div class="p-5">
                        @forelse ($anggota->kunjungan as $kunjungan)
                            <div class="flex items-start {{ !$loop->first ? 'mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400' : '' }}">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-none">
                                    <i data-feather="activity" class="w-5 h-5 text-primary"></i>
                                </div>
                                <div class="ml-3 mr-auto">
                                    <div class="font-medium">{{ $kunjungan->keluhan }}</div>
                                    <div class="text-slate-500 text-xs mt-1">{{ $kunjungan->tanggal->format('d/m/Y') }} {{ $kunjungan->jam ?: '' }} - {{ ucfirst($kunjungan->status) }}</div>
                                    <div class="text-slate-600 mt-2">{{ $kunjungan->tindakan ?: 'Belum ada tindakan tercatat.' }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-slate-500 py-8">Belum ada kunjungan UKS.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div id="mcu" class="tab-pane" role="tabpanel" aria-labelledby="mcu-tab">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Histori Hasil MCU</h2>
                </div>
                <div class="overflow-x-auto p-5">
                    <table class="table table-report">
                        <thead>
                            <tr>
                                <th>Semester</th>
                                <th>Tahun</th>
                                <th>BB/TB</th>
                                <th>BMI</th>
                                <th>Penglihatan</th>
                                <th>Pendengaran</th>
                                <th>Gigi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($anggota->pemeriksaan as $item)
                                <tr>
                                    <td>{{ $item->semester }}</td>
                                    <td>{{ $item->tahun_ajaran }}</td>
                                    <td>{{ $item->berat_badan ?? '-' }} kg / {{ $item->tinggi_badan ?? '-' }} cm</td>
                                    <td>{{ $item->bmi ?? '-' }}</td>
                                    <td>{{ $item->penglihatan_kiri ?: '-' }} / {{ $item->penglihatan_kanan ?: '-' }}</td>
                                    <td>{{ $item->pendengaran ? ucfirst($item->pendengaran) : '-' }}</td>
                                    <td>{{ $item->kondisi_gigi ? ucfirst(str_replace('_', ' ', $item->kondisi_gigi)) : '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('pemeriksaan.show', ['pemeriksaan' => $item, 'back' => 'profile']) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                                        <a href="{{ route('pemeriksaan.raport', $item) }}" class="btn btn-sm btn-primary ml-2">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500 py-8">Belum ada hasil MCU.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="riwayat" class="tab-pane" role="tabpanel" aria-labelledby="riwayat-tab">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Riwayat Penyakit</h2>
                </div>
                <div class="p-5">
                    @forelse ($anggota->riwayatPenyakit as $item)
                        <div class="flex items-start {{ !$loop->first ? 'mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400' : '' }}">
                            <div class="w-10 h-10 rounded-full {{ $item->status === 'sembuh' ? 'bg-success/10' : 'bg-warning/10' }} flex items-center justify-center flex-none">
                                <i data-feather="{{ $item->status === 'sembuh' ? 'check-circle' : 'alert-circle' }}" class="w-5 h-5 {{ $item->status === 'sembuh' ? 'text-success' : 'text-warning' }}"></i>
                            </div>
                            <div class="ml-3">
                                <div class="font-medium">{{ $item->nama_penyakit }}</div>
                                <div class="text-slate-500 text-xs mt-1">
                                    {{ $item->kode_icd ?: 'Tanpa kode ICD' }} - {{ $item->tgl_mulai->format('d/m/Y') }} sampai {{ optional($item->tgl_sembuh)->format('d/m/Y') ?: 'berjalan' }}
                                </div>
                                <div class="mt-2 capitalize">Status: {{ $item->status }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-8">Belum ada riwayat penyakit.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="modal-mcu-siswa" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('pemeriksaan.store') }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="anggota.show">
                    <input type="hidden" name="anggota_id" value="{{ $anggota->id }}">
                    <input type="hidden" name="semester" value="{{ $semesterBerjalan }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">

                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Input MCU {{ $anggota->nama }}</h2>
                    </div>
                    <div class="modal-body p-5">
                        <div class="alert alert-primary-soft show flex items-center mb-5" role="alert">
                            <i data-feather="info" class="w-5 h-5 mr-2"></i>
                            Semester {{ $semesterBerjalan }} tahun ajaran {{ $tahunAjaran }} sudah otomatis untuk siswa ini.
                        </div>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="profile-berat_badan" class="form-label">Berat Badan (kg)</label>
                                <input id="profile-berat_badan" name="berat_badan" type="number" min="1" max="200" step="0.1" class="form-control" placeholder="70">
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="profile-tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                                <input id="profile-tinggi_badan" name="tinggi_badan" type="number" min="50" max="250" step="0.1" class="form-control" placeholder="160">
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="profile-penglihatan_kiri" class="form-label">Penglihatan Kiri</label>
                                <input id="profile-penglihatan_kiri" name="penglihatan_kiri" type="text" class="form-control" placeholder="1.0 / 0.8">
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="profile-penglihatan_kanan" class="form-label">Penglihatan Kanan</label>
                                <input id="profile-penglihatan_kanan" name="penglihatan_kanan" type="text" class="form-control" placeholder="1.0 / 0.8">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="profile-pendengaran" class="form-label">Pendengaran</label>
                                <select id="profile-pendengaran" name="pendengaran" class="form-select">
                                    <option value="">Pilih kondisi</option>
                                    <option value="normal">Normal</option>
                                    <option value="kurang">Kurang</option>
                                    <option value="tuli">Tuli</option>
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="profile-kondisi_gigi" class="form-label">Kondisi Gigi</label>
                                <select id="profile-kondisi_gigi" name="kondisi_gigi" class="form-select">
                                    <option value="">Pilih kondisi</option>
                                    <option value="baik">Baik</option>
                                    <option value="caries">Caries</option>
                                    <option value="perlu_perawatan">Perlu Perawatan</option>
                                </select>
                            </div>
                            <div class="col-span-12">
                                <label for="profile-catatan" class="form-label">Catatan</label>
                                <textarea id="profile-catatan" name="catatan" class="form-control" rows="3" placeholder="Catatan untuk orang tua atau tindak lanjut UKS..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Batal</button>
                        <button type="submit" class="btn btn-primary w-24">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
