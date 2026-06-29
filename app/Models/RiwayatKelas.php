<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatKelas extends Model
{
    protected $table = 'riwayat_kelas';

    protected $fillable = [
        'anggota_id', 'jenjang_id', 'tahun_ajaran', 'kelas_lama', 'kelas_baru', 'aksi', 'diproses_pada',
    ];

    protected $casts = [
        'diproses_pada' => 'datetime',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class);
    }
}
