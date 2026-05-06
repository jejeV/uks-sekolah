<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi ke kunjungan UKS
    public function kunjungan()
    {
        return $this->hasMany(\App\Models\KunjunganUks::class, 'petugas_id');
    }

    public function pemeriksaan()
    {
        return $this->hasMany(\App\Models\PemeriksaanKesehatan::class, 'petugas_id');
    }
}
