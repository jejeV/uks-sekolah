<div id="modal-tambah" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.kunjungan.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Tambah Kunjungan UKS</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 p-5">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Anggota <span class="text-danger">*</span></label>
                        <input type="hidden" name="anggota_id" data-anggota-id required>
                        <input type="text" class="form-control" list="anggota-options-kunjungan"
                               data-anggota-search data-anggota-list="anggota-options-kunjungan"
                               placeholder="Ketik nama atau NIS" autocomplete="off" required>
                        <datalist id="anggota-options-kunjungan"></datalist>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="form-label">Jam</label>
                        <input type="time" name="jam" class="form-control" value="{{ now()->format('H:i') }}">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Keluhan <span class="text-danger">*</span></label>
                        <textarea name="keluhan" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Tindakan</label>
                        <textarea name="tindakan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Obat</label>
                        <input type="text" name="obat" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="ringan">Ringan</option>
                            <option value="sedang">Sedang</option>
                            <option value="berat">Berat</option>
                            <option value="dirujuk">Dirujuk</option>
                        </select>
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

<div id="modal-tambah-pemeriksaan" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="{{ route('pemeriksaan.store') }}">
                @csrf
                <input type="hidden" name="redirect_to" value="dashboard">
                <div class="modal-header">
                    <div>
                        <h2 class="font-medium text-base">Input MCU Siswa</h2>
                        <div class="text-xs text-slate-500 mt-1">Semester {{ $semester_berjalan }} / Tahun ajaran {{ $tahun_ajaran_berjalan }}</div>
                    </div>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 p-5">
                    <input type="hidden" name="semester" value="{{ $semester_berjalan }}">
                    <input type="hidden" name="tahun_ajaran" value="{{ $tahun_ajaran_berjalan }}">
                    <div class="col-span-12">
                        <label class="form-label">Siswa <span class="text-danger">*</span></label>
                        <input type="hidden" name="anggota_id" data-anggota-id required>
                        <input type="text" class="form-control" list="anggota-options-pemeriksaan"
                               data-anggota-search data-anggota-list="anggota-options-pemeriksaan"
                               data-anggota-tipe="siswa" placeholder="Ketik nama atau NIS siswa"
                               autocomplete="off" required>
                        <datalist id="anggota-options-pemeriksaan"></datalist>
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input name="berat_badan" type="number" class="form-control" min="1" max="200" step="0.1">
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input name="tinggi_badan" type="number" class="form-control" min="50" max="250" step="0.1">
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="form-label">Penglihatan Kiri</label>
                        <input name="penglihatan_kiri" type="text" class="form-control" placeholder="1.0">
                    </div>
                    <div class="col-span-6 sm:col-span-3">
                        <label class="form-label">Penglihatan Kanan</label>
                        <input name="penglihatan_kanan" type="text" class="form-control" placeholder="1.0">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Pendengaran</label>
                        <select name="pendengaran" class="form-select">
                            <option value="">Pilih kondisi</option>
                            <option value="normal">Normal</option>
                            <option value="kurang">Kurang</option>
                            <option value="tuli">Tuli</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">Kondisi Gigi</label>
                        <select name="kondisi_gigi" class="form-select">
                            <option value="">Pilih kondisi</option>
                            <option value="baik">Baik</option>
                            <option value="caries">Caries</option>
                            <option value="perlu_perawatan">Perlu Perawatan</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
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
