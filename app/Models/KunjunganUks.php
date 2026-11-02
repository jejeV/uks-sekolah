<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public const STATUS = ['ringan', 'sedang', 'berat', 'dirujuk'];

    public const STATUS_PRIORITAS = ['berat', 'dirujuk'];

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

    /**
     * Terapkan filter anggota (jenjang_id, kelas, search) lewat satu whereHas.
     */
    public function scopeFilterAnggota(Builder $query, array $filters): Builder
    {
        $filters = array_filter(array_intersect_key($filters, array_flip(['jenjang_id', 'kelas', 'search'])));

        return $query->when($filters, fn ($q) => $q->whereHas('anggota', fn ($anggota) => $anggota->filter($filters)));
    }
}
