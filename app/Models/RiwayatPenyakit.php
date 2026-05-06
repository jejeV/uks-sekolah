<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPenyakit extends Model
{
    protected $table = 'riwayat_penyakit';
    protected $fillable = [
        'anggota_id', 'kunjungan_id', 'nama_penyakit',
        'kode_icd', 'tgl_mulai', 'tgl_sembuh', 'status',
    ];

    protected $casts = [
        'tgl_mulai'   => 'date',
        'tgl_sembuh'  => 'date',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function kunjungan()
    {
        return $this->belongsTo(KunjunganUks::class, 'kunjungan_id');
    }
}
