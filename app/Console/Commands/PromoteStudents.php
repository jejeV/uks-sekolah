<?php

namespace App\Console\Commands;

use App\Models\Anggota;
use App\Models\RiwayatKelas;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromoteStudents extends Command
{
    protected $signature = 'uks:promote-students
                            {--execute : Jalankan perubahan. Tanpa opsi ini hanya menampilkan pratinjau.}
                            {--date= : Tanggal proses dalam format YYYY-MM-DD, untuk pratinjau atau pengujian.}';

    protected $description = 'Menaikkan kelas siswa pada awal tahun ajaran dan meluluskan siswa SMP kelas 9.';

    public function handle(): int
    {
        $processedAt = $this->option('date') ? Carbon::parse($this->option('date'))->startOfDay() : now();
        $tahunAjaranSelesai = $processedAt->year - 1;
        $execute = $this->option('execute');
        $total = 0;

        Anggota::with('jenjang')
            ->where('aktif', true)
            ->where('tipe', 'siswa')
            ->orderBy('nama')
            ->each(function (Anggota $siswa) use ($tahunAjaranSelesai, $processedAt, $execute, &$total) {
                $perubahan = $this->tentukanPerubahan($siswa);

                if (!$perubahan || RiwayatKelas::where([
                    'anggota_id' => $siswa->id,
                    'tahun_ajaran' => $tahunAjaranSelesai,
                    'aksi' => $perubahan['aksi'],
                ])->exists()) {
                    return;
                }

                $total++;
                $this->line(sprintf(
                    '%s: %s%s',
                    $siswa->nama,
                    $siswa->kelas ?: '-',
                    $perubahan['aksi'] === 'lulus' ? ' → LULUS' : ' → ' . $perubahan['kelas_baru']
                ));

                if (!$execute) {
                    return;
                }

                DB::transaction(function () use ($siswa, $perubahan, $tahunAjaranSelesai, $processedAt) {
                    RiwayatKelas::create([
                        'anggota_id' => $siswa->id,
                        'jenjang_id' => $siswa->jenjang_id,
                        'tahun_ajaran' => $tahunAjaranSelesai,
                        'kelas_lama' => $siswa->kelas,
                        'kelas_baru' => $perubahan['kelas_baru'],
                        'aksi' => $perubahan['aksi'],
                        'diproses_pada' => $processedAt,
                    ]);

                    $siswa->update([
                        'kelas' => $perubahan['kelas_baru'],
                        'aktif' => $perubahan['aktif'],
                    ]);
                });
            });

        $this->info($execute
            ? $total . ' siswa berhasil diproses.'
            : $total . ' siswa akan diproses. Jalankan dengan --execute untuk menyimpan perubahan.');

        return self::SUCCESS;
    }

    private function tentukanPerubahan(Anggota $siswa): ?array
    {
        $jenjang = optional($siswa->jenjang)->nama;
        $kelas = trim((string) $siswa->kelas);

        if ($jenjang === 'TK' || !preg_match('/^(\d)(.*)$/', $kelas, $matches)) {
            return null;
        }

        $tingkat = (int) $matches[1];
        $suffix = $matches[2];

        if ($jenjang === 'SD' && $tingkat >= 1 && $tingkat <= 5) {
            return ['aksi' => 'naik_kelas', 'kelas_baru' => ($tingkat + 1) . $suffix, 'aktif' => true];
        }

        if ($jenjang === 'SMP' && $tingkat >= 7 && $tingkat <= 8) {
            return ['aksi' => 'naik_kelas', 'kelas_baru' => ($tingkat + 1) . $suffix, 'aktif' => true];
        }

        if ($jenjang === 'SMP' && $tingkat === 9) {
            return ['aksi' => 'lulus', 'kelas_baru' => $kelas, 'aktif' => false];
        }

        return null;
    }
}
