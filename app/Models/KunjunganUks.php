<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KunjunganUks extends Model
{
    protected $table = 'kunjungan_uks';
    protected $fillable = [
        'anggota_id', 'petugas_id', 'tanggal', 'jam',
        'keluhan', 'diagnosis', 'tindakan', 'obat', 'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function riwayatPenyakit()
    {
        return $this->hasMany(RiwayatPenyakit::class, 'kunjungan_id');
    }
}
