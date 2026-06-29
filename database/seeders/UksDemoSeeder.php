<?php
namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Jenjang;
use App\Models\KunjunganUks;
use App\Models\PemeriksaanKesehatan;
use App\Models\RiwayatPenyakit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UksDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::updateOrCreate(
                ['email' => 'admin@sekolah.sch.id'],
                [
                    'name' => 'Admin UKS',
                    'password' => Hash::make('admin123'),
                    'role' => 'admin',
                ]
            );

            $petugas = User::updateOrCreate(
                ['email' => 'petugas@sekolah.sch.id'],
                [
                    'name' => 'Petugas UKS',
                    'password' => Hash::make('petugas123'),
                    'role' => 'petugas',
                ]
            );

            $jenjang = collect(['TK', 'SD', 'SMP'])
                ->mapWithKeys(fn ($nama) => [$nama => Jenjang::firstOrCreate(['nama' => $nama])]);

            $anggota = $this->seedAnggota($jenjang);
            $this->deactivateNonDemoSiswaGuru($anggota->keys()->all());
            $this->seedPemeriksaan($anggota, $admin);
            $this->seedKunjunganSebulan($anggota, $petugas);
            $this->seedRiwayatPenyakit();
        });
    }

    private function seedAnggota($jenjang)
    {
        $anggotaData = [
            ['TK001', 'Samuel Jason Rain', 'siswa', 'TK', 'TK B', 'L', '2020-05-12'],
            ['TK002', 'Alya Putri Maharani', 'siswa', 'TK', 'TK A', 'P', '2020-08-19'],
            ['TK003', 'Raka Pratama', 'siswa', 'TK', 'TK B', 'L', '2019-11-02'],
            ['TK004', 'Luna Safitri', 'siswa', 'TK', 'TK A', 'P', '2020-10-20'],
            ['TK005', 'Aditia Pranata', 'siswa', 'TK', 'TK A', 'L', '2020-02-16'],
            ['TK006', 'Maira Khansa', 'siswa', 'TK', 'TK B', 'P', '2019-07-03'],
            ['TK007', 'Rizky Ardiansyah', 'siswa', 'TK', 'TK A', 'L', '2020-03-25'],
            ['TK008', 'Nayla Humaira', 'siswa', 'TK', 'TK B', 'P', '2019-12-11'],
            ['SD001', 'Nabila Azzahra', 'siswa', 'SD', '1A', 'P', '2018-04-21'],
            ['SD002', 'Bima Saputra', 'siswa', 'SD', '2B', 'L', '2017-02-10'],
            ['SD003', 'Keisha Amanda', 'siswa', 'SD', '3A', 'P', '2016-09-13'],
            ['SD004', 'Farel Hidayat', 'siswa', 'SD', '4B', 'L', '2015-12-01'],
            ['SD005', 'Zahra Lestari', 'siswa', 'SD', '5A', 'P', '2014-07-26'],
            ['SD006', 'Daffa Ramadhan', 'siswa', 'SD', '6B', 'L', '2013-10-17'],
            ['SD007', 'Anisa Kirana', 'siswa', 'SD', '4A', 'P', '2015-06-07'],
            ['SD008', 'Alvaro Mahendra', 'siswa', 'SD', '1B', 'L', '2018-01-19'],
            ['SD009', 'Celine Putri', 'siswa', 'SD', '2A', 'P', '2017-09-09'],
            ['SD010', 'Rangga Prakoso', 'siswa', 'SD', '3B', 'L', '2016-05-30'],
            ['SD011', 'Mikha Salsabila', 'siswa', 'SD', '4A', 'P', '2015-03-18'],
            ['SD012', 'Fathir Alamsyah', 'siswa', 'SD', '5B', 'L', '2014-11-12'],
            ['SD013', 'Kirana Aulia', 'siswa', 'SD', '6A', 'P', '2013-06-04'],
            ['SD014', 'Rafa Nugraha', 'siswa', 'SD', '1A', 'L', '2018-08-28'],
            ['SD015', 'Tiara Lestari', 'siswa', 'SD', '2B', 'P', '2017-12-15'],
            ['SD016', 'Arya Baskara', 'siswa', 'SD', '3A', 'L', '2016-02-22'],
            ['SMP001', 'Rafi Alfarizi', 'siswa', 'SMP', '7A', 'L', '2012-03-14'],
            ['SMP002', 'Mikayla Putri', 'siswa', 'SMP', '7B', 'P', '2012-06-05'],
            ['SMP003', 'Arkan Wijaya', 'siswa', 'SMP', '8A', 'L', '2011-01-28'],
            ['SMP004', 'Citra Anindya', 'siswa', 'SMP', '8B', 'P', '2011-09-09'],
            ['SMP005', 'Gilang Permana', 'siswa', 'SMP', '9A', 'L', '2010-02-22'],
            ['SMP006', 'Salsabila Nur', 'siswa', 'SMP', '9B', 'P', '2010-11-30'],
            ['SMP007', 'Bagas Mahendra', 'siswa', 'SMP', '8C', 'L', '2011-07-15'],
            ['SMP008', 'Dinda Maharani', 'siswa', 'SMP', '7C', 'P', '2012-04-01'],
            ['SMP009', 'Yusuf Ramadhan', 'siswa', 'SMP', '8A', 'L', '2011-10-07'],
            ['SMP010', 'Aurelia Safira', 'siswa', 'SMP', '9B', 'P', '2010-05-21'],
            ['SMP011', 'Kevin Pratama', 'siswa', 'SMP', '7A', 'L', '2012-08-12'],
            ['SMP012', 'Nadya Oktaviani', 'siswa', 'SMP', '8B', 'P', '2011-12-03'],
            ['SMP013', 'Ilham Fauzan', 'siswa', 'SMP', '9A', 'L', '2010-09-25'],
            ['SMP014', 'Mutiara Putri', 'siswa', 'SMP', '7B', 'P', '2012-01-17'],
            ['SMP015', 'Dimas Aryanto', 'siswa', 'SMP', '8C', 'L', '2011-04-29'],
            ['SMP016', 'Syifa Rahmadani', 'siswa', 'SMP', '9C', 'P', '2010-07-08'],
            ['G001', 'Ibu Ratna Sari', 'guru', 'SD', null, 'P', '1987-05-08'],
            ['G002', 'Pak Andi Prasetyo', 'guru', 'SMP', null, 'L', '1984-01-16'],
            ['G003', 'Ibu Maya Lestari', 'guru', 'TK', null, 'P', '1990-03-25'],
            ['G004', 'Pak Budi Santoso', 'guru', 'SD', null, 'L', '1982-08-11'],
            ['G005', 'Ibu Wulan Kartika', 'guru', 'SMP', null, 'P', '1989-12-04'],
            ['G006', 'Pak Hendra Saputra', 'guru', 'TK', null, 'L', '1985-04-18'],
            ['G007', 'Ibu Rina Kurniasih', 'guru', 'SD', null, 'P', '1991-02-27'],
            ['G008', 'Pak Yoga Firmansyah', 'guru', 'SMP', null, 'L', '1986-06-13'],
            ['G009', 'Ibu Desi Anggraini', 'guru', 'SD', null, 'P', '1988-09-22'],
            ['G010', 'Pak Fikri Maulana', 'guru', 'SMP', null, 'L', '1983-11-05'],
        ];

        return collect($anggotaData)->mapWithKeys(function ($row) use ($jenjang) {
            [$nisNip, $nama, $tipe, $jenjangNama, $kelas, $jenisKelamin, $tglLahir] = $row;

            return [
                $nisNip => Anggota::updateOrCreate(
                    ['nis_nip' => $nisNip],
                    [
                        'jenjang_id' => $jenjang[$jenjangNama]->id,
                        'nama' => $nama,
                        'tipe' => $tipe,
                        'kelas' => $kelas,
                        'tgl_lahir' => $tglLahir,
                        'jenis_kelamin' => $jenisKelamin,
                        'aktif' => true,
                    ]
                ),
            ];
        });
    }

    private function deactivateNonDemoSiswaGuru(array $demoNisNip): void
    {
        Anggota::whereIn('tipe', ['siswa', 'guru'])
            ->whereNotIn('nis_nip', $demoNisNip)
            ->update(['aktif' => false]);
    }

    private function seedPemeriksaan($anggota, User $admin): void
    {
        $semester = now()->month >= 7 ? 1 : 2;
        $tahunAjaran = now()->month >= 7 ? now()->year : now()->year - 1;

        $anggota
            ->filter(fn (Anggota $item) => in_array($item->tipe, ['siswa', 'guru'], true))
            ->values()
            ->each(function (Anggota $item, int $index) use ($admin, $semester, $tahunAjaran) {
                $shouldCreateRaport = $item->tipe === 'guru'
                    ? $index % 3 !== 0
                    : $index % 4 !== 0;

                if (!$shouldCreateRaport) {
                    return;
                }

                $tinggi = $item->tipe === 'guru'
                    ? 155 + (($index % 8) * 3)
                    : 105 + (($index % 18) * 4);
                $berat = $item->tipe === 'guru'
                    ? 52 + (($index % 9) * 4)
                    : 17 + (($index % 18) * 2.2);
                $tinggiMeter = $tinggi / 100;

                PemeriksaanKesehatan::updateOrCreate(
                    [
                        'anggota_id' => $item->id,
                        'semester' => $semester,
                        'tahun_ajaran' => $tahunAjaran,
                    ],
                    [
                        'petugas_id' => $admin->id,
                        'berat_badan' => round($berat, 2),
                        'tinggi_badan' => round($tinggi, 2),
                        'bmi' => round($berat / ($tinggiMeter * $tinggiMeter), 2),
                        'penglihatan_kiri' => $index % 6 === 0 ? '0.8' : '1.0',
                        'penglihatan_kanan' => $index % 5 === 0 ? '0.9' : '1.0',
                        'pendengaran' => $index % 9 === 0 ? 'kurang' : 'normal',
                        'kondisi_gigi' => $index % 5 === 0 ? 'caries' : 'baik',
                        'gula_darah' => $item->tipe === 'guru' ? 92 + (($index % 7) * 9) : null,
                        'kolesterol' => $item->tipe === 'guru' ? 165 + (($index % 8) * 12) : null,
                        'catatan' => $index % 5 === 0
                            ? 'Perlu kontrol ulang pada pemeriksaan berikutnya.'
                            : 'Kondisi umum baik.',
                    ]
                );
            });
    }

    private function seedKunjunganSebulan($anggota, User $petugas): void
    {
        $keluhanData = [
            ['Batuk dan pilek', 'ISPA ringan', 'Istirahat, masker, dan observasi', 'Vitamin C'],
            ['Pusing setelah aktivitas', 'Dehidrasi ringan', 'Minum air dan istirahat', 'Oralit'],
            ['Sakit perut', 'Dispepsia ringan', 'Kompres hangat dan pantau', 'Antasida'],
            ['Demam', 'Febris', 'Kompres dan observasi suhu', 'Paracetamol'],
            ['Luka lecet', 'Abrasi ringan', 'Bersihkan luka dan tutup plester', 'Povidone iodine'],
            ['Mimisan', 'Epistaksis', 'Tekan hidung dan observasi', null],
            ['Sesak napas', 'Asma kambuh', 'Observasi ketat dan hubungi orang tua', 'Salbutamol'],
            ['Terkilir', 'Sprain ankle', 'Kompres dingin dan imobilisasi ringan', null],
        ];
        $statusPool = ['ringan', 'ringan', 'ringan', 'sedang', 'sedang', 'berat', 'dirujuk'];
        $anggotaList = $anggota->values();
        $startDate = now()->copy()->subDays(29)->startOfDay();
        $sequence = 1;

        foreach (range(0, 29) as $dayOffset) {
            $date = $startDate->copy()->addDays($dayOffset);
            $dailyTotal = $date->isMonday() ? 7 : ($date->isFriday() ? 3 : 2);

            foreach (range(1, $dailyTotal) as $visitIndex) {
                $anggotaItem = $anggotaList[($sequence + $visitIndex) % $anggotaList->count()];
                [$keluhan, $diagnosis, $tindakan, $obat] = $keluhanData[($sequence + $visitIndex) % count($keluhanData)];
                $status = $statusPool[($sequence + $visitIndex + ($date->isMonday() ? 2 : 0)) % count($statusPool)];
                $jam = sprintf('%02d:%02d', 8 + (($visitIndex + $sequence) % 5), ($visitIndex * 10) % 60);

                KunjunganUks::updateOrCreate(
                    [
                        'anggota_id' => $anggotaItem->id,
                        'tanggal' => $date->toDateString(),
                        'jam' => $jam,
                    ],
                    [
                        'petugas_id' => $petugas->id,
                        'keluhan' => $keluhan,
                        'diagnosis' => $diagnosis,
                        'tindakan' => $tindakan,
                        'obat' => $obat,
                        'status' => $status,
                        'created_at' => Carbon::parse($date->toDateString() . ' ' . $jam),
                        'updated_at' => Carbon::parse($date->toDateString() . ' ' . $jam),
                    ]
                );
            }

            $sequence += $dailyTotal;
        }
    }

    private function seedRiwayatPenyakit(): void
    {
        KunjunganUks::whereNotNull('diagnosis')
            ->whereIn('status', ['sedang', 'berat', 'dirujuk'])
            ->latest('tanggal')
            ->take(20)
            ->get()
            ->each(function (KunjunganUks $kunjungan, int $index) {
                RiwayatPenyakit::updateOrCreate(
                    [
                        'anggota_id' => $kunjungan->anggota_id,
                        'kunjungan_id' => $kunjungan->id,
                    ],
                    [
                        'nama_penyakit' => $kunjungan->diagnosis,
                        'kode_icd' => ['J00', 'R50', 'J45', 'K30', 'S80', 'R04'][$index % 6],
                        'tgl_mulai' => $kunjungan->tanggal,
                        'tgl_sembuh' => $kunjungan->status === 'sedang'
                            ? $kunjungan->tanggal->copy()->addDays(2)
                            : null,
                        'status' => $kunjungan->status === 'sedang'
                            ? 'sembuh'
                            : ($kunjungan->status === 'berat' ? 'kronis' : 'aktif'),
                    ]
                );
            });
    }
}
