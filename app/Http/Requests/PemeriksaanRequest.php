<?php

namespace App\Http\Requests;

use App\Models\Anggota;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Foundation\Http\FormRequest;

class PemeriksaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id'        => 'required|exists:anggota,id',
            'semester'          => 'required|in:1,2',
            'tahun_ajaran'      => 'required|digits:4',
            'berat_badan'       => 'nullable|numeric|min:1|max:200',
            'tinggi_badan'      => 'nullable|numeric|min:50|max:250',
            'penglihatan_kiri'  => 'nullable|string|max:10',
            'penglihatan_kanan' => 'nullable|string|max:10',
            'pendengaran'       => 'nullable|in:normal,kurang,tuli',
            'kondisi_gigi'      => 'nullable|in:baik,caries,perlu_perawatan',
            'gula_darah'        => 'nullable|numeric|min:0|max:1000',
            'kolesterol'        => 'nullable|numeric|min:0|max:1000',
            'catatan'           => 'nullable|string',
            'redirect_to'       => 'nullable|in:dashboard,pemeriksaan.index,anggota.show',
            'lanjut'            => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'anggota_id.required' => 'Pilih siswa/guru dari daftar saran yang muncul.',
            'berat_badan.*'       => 'Berat badan harus angka antara 1-200 kg.',
            'tinggi_badan.*'      => 'Tinggi badan harus angka antara 50-250 cm.',
            'gula_darah.*'        => 'Gula darah harus angka antara 0-1000 mg/dL.',
            'kolesterol.*'        => 'Kolesterol harus angka antara 0-1000 mg/dL.',
        ];
    }

    /**
     * Data siap simpan: BMI dihitung otomatis, cek lab hanya untuk guru.
     */
    public function pemeriksaanData(Anggota $anggota): array
    {
        $data = $this->safe()->except(['redirect_to', 'lanjut']);
        $data['bmi'] = PemeriksaanKesehatan::hitungBmi($data['berat_badan'] ?? null, $data['tinggi_badan'] ?? null);

        if ($anggota->tipe !== 'guru') {
            $data['gula_darah'] = null;
            $data['kolesterol'] = null;
        }

        return $data;
    }
}
