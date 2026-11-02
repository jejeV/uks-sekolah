<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $anggota = $this->route('anggota');

        return [
            'jenjang_id'    => 'required|exists:jenjang,id',
            'nis_nip'       => ['required', 'string', Rule::unique('anggota', 'nis_nip')->ignore($anggota)],
            'nama'          => 'required|string|max:100',
            'tipe'          => 'required|in:siswa,guru,tenaga_kependidikan',
            'kelas'         => 'nullable|string|max:20',
            'tgl_lahir'     => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'aktif'         => 'nullable|boolean',
            'redirect_to'   => 'nullable|in:dashboard,anggota.index',
        ];
    }

    public function messages(): array
    {
        return [
            'jenjang_id.required'    => 'Jenjang wajib dipilih.',
            'nis_nip.required'       => 'NIS / NIP wajib diisi.',
            'nis_nip.unique'         => 'NIS / NIP sudah dipakai anggota lain.',
            'nama.required'          => 'Nama wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ];
    }

    public function anggotaData(): array
    {
        $data = $this->safe()->except(['redirect_to', 'aktif']);

        if ($this->has('aktif')) {
            $data['aktif'] = $this->boolean('aktif');
        }

        return $data;
    }
}
