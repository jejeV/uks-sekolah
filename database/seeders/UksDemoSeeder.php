<?php
namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Jenjang;
use App\Models\KunjunganUks;
use App\Models\PemeriksaanKesehatan;
use App\Models\RiwayatPenyakit;
use App\Models\User;
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

            $anggotaData = [
                ['TK001', 'Samuel Jason Rain', 'siswa', 'TK', 'TK B', 'L', '2020-05-12'],
                ['TK002', 'Alya Putri Maharani', 'siswa', 'TK', 'TK A', 'P', '2020-08-19'],
                ['TK003', 'Raka Pratama', 'siswa', 'TK', 'TK B', 'L', '2019-11-02'],
                ['SD001', 'Nabila Azzahra', 'siswa', 'SD', '1A', 'P', '2018-04-21'],
                ['SD002', 'Bima Saputra', 'siswa', 'SD', '2B', 'L', '2017-02-10'],
                ['SD003', 'Keisha Amanda', 'siswa', 'SD', '3A', 'P', '2016-09-13'],
                ['SD004', 'Farel Hidayat', 'siswa', 'SD', '4B', 'L', '2015-12-01'],
                ['SD005', 'Zahra Lestari', 'siswa', 'SD', '5A', 'P', '2014-07-26'],
                ['SD006', 'Daffa Ramadhan', 'siswa', 'SD', '6B', 'L', '2013-10-17'],
                ['SMP001', 'Rafi Alfarizi', 'siswa', 'SMP', '7A', 'L', '2012-03-14'],
                ['SMP002', 'Mikayla Putri', 'siswa', 'SMP', '7B', 'P', '2012-06-05'],
                ['SMP003', 'Arkan Wijaya', 'siswa', 'SMP', '8A', 'L', '2011-01-28'],
                ['SMP004', 'Citra Anindya', 'siswa', 'SMP', '8B', 'P', '2011-09-09'],
                ['SMP005', 'Gilang Permana', 'siswa', 'SMP', '9A', 'L', '2010-02-22'],
                ['SMP006', 'Salsabila Nur', 'siswa', 'SMP', '9B', 'P', '2010-11-30'],
                ['G001', 'Ibu Ratna Sari', 'guru', 'SD', null, 'P', '1987-05-08'],
                ['G002', 'Pak Andi Prasetyo', 'guru', 'SMP', null, 'L', '1984-01-16'],
                ['G003', 'Ibu Maya Lestari', 'guru', 'TK', null, 'P', '1990-03-25'],
                ['S001', 'Pak Budi Santoso', 'tenaga_kependidikan', 'SD', null, 'L', '1982-08-11'],
                ['S002', 'Ibu Wulan Kartika', 'tenaga_kependidikan', 'SMP', null, 'P', '1989-12-04'],
                ['S003', 'Pak Hendra Saputra', 'tenaga_kependidikan', 'TK', null, 'L', '1985-04-18'],
                ['SD007', 'Anisa Kirana', 'siswa', 'SD', '4A', 'P', '2015-06-07'],
                ['SMP007', 'Bagas Mahendra', 'siswa', 'SMP', '8C', 'L', '2011-07-15'],
                ['TK004', 'Luna Safitri', 'siswa', 'TK', 'TK A', 'P', '2020-10-20'],
            ];

            $anggota = collect($anggotaData)->mapWithKeys(function ($row) use ($jenjang) {
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

            $kunjunganData = [
                ['TK001', 0, '08:05', 'Batuk ringan dan pilek', 'ISPA ringan', 'Istirahat dan observasi', 'Vitamin C', 'ringan'],
                ['SD002', 0, '09:15', 'Pusing setelah olahraga', 'Dehidrasi ringan', 'Minum oralit dan istirahat', 'Oralit', 'sedang'],
                ['SMP003', 1, '10:20', 'Sesak napas saat pelajaran', 'Asma kambuh', 'Nebulizer dan hubungi orang tua', 'Salbutamol', 'berat'],
                ['SD005', 1, '11:00', 'Sakit perut', 'Dispepsia ringan', 'Kompres hangat dan pantau', 'Antasida', 'ringan'],
                ['SMP006', 2, '08:40', 'Demam dan menggigil', 'Febris', 'Kompres dan rujuk bila naik', 'Paracetamol', 'sedang'],
                ['TK003', 2, '09:35', 'Luka lecet di lutut', 'Abrasi ringan', 'Bersihkan luka dan plester', 'Povidone iodine', 'ringan'],
                ['SD004', 3, '10:10', 'Mimisan', 'Epistaksis', 'Tekan hidung dan observasi', null, 'sedang'],
                ['SMP001', 3, '12:30', 'Nyeri dada saat aktivitas', 'Perlu pemeriksaan lanjutan', 'Rujuk ke fasilitas kesehatan', null, 'dirujuk'],
                ['SD001', 4, '08:25', 'Mual dan lemas', 'Gastritis ringan', 'Istirahat dan makan ringan', 'Antasida', 'ringan'],
                ['SMP002', 4, '09:55', 'Sakit kepala', 'Cephalgia ringan', 'Istirahat di UKS', 'Paracetamol', 'ringan'],
                ['TK002', 5, '10:45', 'Gatal kemerahan', 'Dermatitis ringan', 'Bersihkan area dan observasi', 'Calamine', 'ringan'],
                ['SD006', 5, '11:15', 'Demam tinggi', 'Febris tinggi', 'Hubungi orang tua', 'Paracetamol', 'berat'],
                ['SMP005', 6, '09:05', 'Terkilir pergelangan kaki', 'Sprain ankle', 'Kompres dingin dan imobilisasi', null, 'sedang'],
                ['SD003', 7, '08:50', 'Sakit gigi', 'Karies gigi', 'Edukasi kebersihan gigi', null, 'ringan'],
                ['SMP004', 8, '10:00', 'Nyeri haid', 'Dismenore', 'Istirahat dan kompres hangat', 'Ibuprofen', 'sedang'],
                ['SD007', 9, '10:25', 'Batuk pilek', 'Common cold', 'Masker dan istirahat', 'Vitamin C', 'ringan'],
                ['SMP007', 10, '11:40', 'Pusing berputar', 'Vertigo ringan', 'Observasi tekanan darah', null, 'sedang'],
                ['TK004', 11, '09:20', 'Terjatuh saat bermain', 'Memar ringan', 'Kompres dingin', null, 'ringan'],
                ['SD005', 16, '10:10', 'Demam', 'Febris', 'Kompres hangat', 'Paracetamol', 'ringan'],
                ['SMP001', 20, '12:05', 'Asma berulang', 'Asma episodik', 'Observasi ketat', 'Salbutamol', 'berat'],
                ['SD002', 28, '08:30', 'Pusing', 'Kelelahan', 'Istirahat', null, 'ringan'],
                ['SMP006', 32, '11:10', 'Demam dan batuk', 'ISPA', 'Edukasi masker', 'Paracetamol', 'sedang'],
            ];

            collect($kunjunganData)->each(function ($row) use ($anggota, $petugas) {
                [$nisNip, $daysAgo, $jam, $keluhan, $diagnosis, $tindakan, $obat, $status] = $row;
                $tanggal = now()->subDays($daysAgo)->toDateString();

                KunjunganUks::updateOrCreate(
                    [
                        'anggota_id' => $anggota[$nisNip]->id,
                        'tanggal' => $tanggal,
                        'jam' => $jam,
                    ],
                    [
                        'petugas_id' => $petugas->id,
                        'keluhan' => $keluhan,
                        'diagnosis' => $diagnosis,
                        'tindakan' => $tindakan,
                        'obat' => $obat,
                        'status' => $status,
                    ]
                );
            });

            collect($anggota)
                ->filter(fn (Anggota $item) => $item->tipe === 'siswa')
                ->take(18)
                ->values()
                ->each(function (Anggota $item, int $index) use ($admin) {
                    $tinggi = 105 + ($index * 3.5);
                    $berat = 17 + ($index * 2.1);
                    $tinggiMeter = $tinggi / 100;

                    PemeriksaanKesehatan::updateOrCreate(
                        [
                            'anggota_id' => $item->id,
                            'semester' => $index % 2 === 0 ? 1 : 2,
                            'tahun_ajaran' => now()->year,
                        ],
                        [
                            'petugas_id' => $admin->id,
                            'berat_badan' => round($berat, 2),
                            'tinggi_badan' => round($tinggi, 2),
                            'bmi' => round($berat / ($tinggiMeter * $tinggiMeter), 2),
                            'penglihatan_kiri' => $index % 5 === 0 ? '0.8' : '1.0',
                            'penglihatan_kanan' => $index % 4 === 0 ? '0.9' : '1.0',
                            'pendengaran' => $index % 7 === 0 ? 'kurang' : 'normal',
                            'kondisi_gigi' => $index % 4 === 0 ? 'caries' : 'baik',
                            'catatan' => $index % 4 === 0 ? 'Perlu kontrol ulang pada pemeriksaan berikutnya.' : 'Kondisi umum baik.',
                        ]
                    );
                });

            KunjunganUks::whereNotNull('diagnosis')->take(12)->get()->each(function (KunjunganUks $kunjungan, int $index) {
                RiwayatPenyakit::updateOrCreate(
                    [
                        'anggota_id' => $kunjungan->anggota_id,
                        'kunjungan_id' => $kunjungan->id,
                    ],
                    [
                        'nama_penyakit' => $kunjungan->diagnosis,
                        'kode_icd' => ['J00', 'R50', 'J45', 'K30', 'S80', 'K02'][$index % 6],
                        'tgl_mulai' => $kunjungan->tanggal,
                        'tgl_sembuh' => $kunjungan->status === 'ringan' ? $kunjungan->tanggal->copy()->addDays(2) : null,
                        'status' => $kunjungan->status === 'ringan' ? 'sembuh' : ($kunjungan->status === 'berat' ? 'kronis' : 'aktif'),
                    ]
                );
            });
        });
    }
}
