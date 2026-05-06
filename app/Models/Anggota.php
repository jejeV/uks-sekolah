<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'anggota';
    protected $fillable = [
        'jenjang_id', 'nis_nip', 'nama', 'tipe',
        'kelas', 'tgl_lahir', 'jenis_kelamin', 'aktif',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'aktif'     => 'boolean',
    ];

    public function jenjang()
    {
        return $this->belongsTo(Jenjang::class);
    }

    public function kunjungan()
    {
        return $this->hasMany(KunjunganUks::class);
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanKesehatan::class);
    }

    public function riwayatPenyakit()
    {
        return $this->hasMany(RiwayatPenyakit::class);
    }
}
