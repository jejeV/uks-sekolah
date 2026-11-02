<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    public const TIPE_LABEL = [
        'siswa'               => 'Siswa',
        'guru'                => 'Guru',
        'tenaga_kependidikan' => 'Tenaga Kependidikan',
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

    public function riwayatKelas()
    {
        return $this->hasMany(RiwayatKelas::class);
    }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_LABEL[$this->tipe] ?? ucfirst((string) $this->tipe);
    }

    /**
     * Label untuk kolom pencarian anggota, mis. "Nabila - SD001 (Siswa / SD / 2A)".
     */
    public function getPickerLabelAttribute(): string
    {
        $detail = array_filter([$this->tipe_label, optional($this->jenjang)->nama, $this->kelas]);

        return $this->nama . ' - ' . $this->nis_nip . ' (' . implode(' / ', $detail) . ')';
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('aktif'), true);
    }

    /**
     * Filter umum dari query string: jenjang_id, kelas, search (nama / NIS / NIP).
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['jenjang_id'] ?? null, fn ($q, $value) => $q->where($q->qualifyColumn('jenjang_id'), $value))
            ->when($filters['kelas'] ?? null, fn ($q, $value) => $q->where($q->qualifyColumn('kelas'), $value))
            ->when($filters['search'] ?? null, function ($q, $value) {
                $q->where(function ($inner) use ($q, $value) {
                    $inner->where($q->qualifyColumn('nama'), 'like', '%' . $value . '%')
                        ->orWhere($q->qualifyColumn('nis_nip'), 'like', '%' . $value . '%');
                });
            });
    }

    public function scopeSudahDiperiksa(Builder $query, int $semester, int $tahunAjaran): Builder
    {
        return $query->whereHas('pemeriksaan', fn ($q) => $q->periode($semester, $tahunAjaran));
    }

    public function scopeBelumDiperiksa(Builder $query, int $semester, int $tahunAjaran): Builder
    {
        return $query->whereDoesntHave('pemeriksaan', fn ($q) => $q->periode($semester, $tahunAjaran));
    }

    /**
     * Urutan tampilan: siswa TK, SD, SMP, lalu guru; kemudian kelas dan nama.
     */
    public function scopeUrutJenjang(Builder $query): Builder
    {
        return $query->leftJoin('jenjang', 'anggota.jenjang_id', '=', 'jenjang.id')
            ->select('anggota.*')
            ->orderByRaw("
                CASE
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'TK' THEN 1
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SD' THEN 2
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SMP' THEN 3
                    WHEN anggota.tipe = 'guru' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('anggota.kelas')
            ->orderBy('anggota.nama');
    }

    public static function kelasOptions($jenjangId = null): Collection
    {
        return static::where('tipe', 'siswa')
            ->when($jenjangId, fn ($query) => $query->where('jenjang_id', $jenjangId))
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');
    }
}
