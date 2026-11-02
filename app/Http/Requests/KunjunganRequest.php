<?php

namespace App\Http\Requests;

use App\Models\KunjunganUks;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KunjunganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id'    => 'required|exists:anggota,id',
            'tanggal'       => 'required|date',
            'jam'           => 'nullable|date_format:H:i',
            'keluhan'       => 'required|string',
            'diagnosis'     => 'nullable|string',
            'tindakan'      => 'nullable|string',
            'obat'          => 'nullable|string|max:200',
            'status'        => ['required', Rule::in(KunjunganUks::STATUS)],
            'nama_penyakit' => 'nullable|string|max:100',
            'kode_icd'      => 'nullable|string|max:10',
            'redirect_to'   => 'nullable|in:dashboard,kunjungan.index,anggota.show',
        ];
    }

    public function messages(): array
    {
        return [
            'anggota_id.required' => 'Pilih siswa/guru dari daftar saran yang muncul.',
            'tanggal.required'    => 'Tanggal kunjungan wajib diisi.',
            'jam.date_format'     => 'Format jam harus JJ:MM, contoh 09:30.',
            'keluhan.required'    => 'Keluhan wajib diisi.',
            'status.required'     => 'Tingkat keluhan wajib dipilih.',
        ];
    }

    public function kunjunganData(): array
    {
        return $this->safe()->only(['anggota_id', 'tanggal', 'jam', 'keluhan', 'diagnosis', 'tindakan', 'obat', 'status']);
    }
}
