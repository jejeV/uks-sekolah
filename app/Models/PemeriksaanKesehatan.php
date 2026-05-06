<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanKesehatan extends Model
{
    protected $table = 'pemeriksaan_kesehatan';
    protected $fillable = [
        'anggota_id', 'petugas_id', 'semester', 'tahun_ajaran',
        'berat_badan', 'tinggi_badan', 'bmi',
        'penglihatan_kiri', 'penglihatan_kanan',
        'pendengaran', 'kondisi_gigi', 'catatan',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
