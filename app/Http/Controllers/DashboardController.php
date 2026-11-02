<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Jenjang;
use App\Models\KunjunganUks;
use App\Models\PemeriksaanKesehatan;
use App\Support\TahunAjaran;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now();
        $semesterBerjalan = TahunAjaran::semester($today);
        $tahunAjaran = TahunAjaran::tahun($today);
        $filters = array_filter($request->only(['jenjang_id', 'kelas']));

        $siswaAktif = fn () => Anggota::aktif()->where('tipe', 'siswa')->filter($filters);
        $kunjungan = fn () => KunjunganUks::filterAnggota($filters);

        $totalSiswa = $siswaAktif()->count();

        // Satu query per status untuk bulan ini; total bulan ini = jumlah semua status.
        $kunjunganPerStatus = $kunjungan()
            ->whereBetween('tanggal', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total);
        $kunjunganBulan = $kunjunganPerStatus->sum();

        $lastMonth = $today->copy()->subMonthNoOverflow();
        $kunjunganBulanLalu = $kunjungan()
            ->whereBetween('tanggal', [$lastMonth->copy()->startOfMonth()->toDateString(), $lastMonth->copy()->endOfMonth()->toDateString()])
            ->count();
        $perubahanKunjungan = $kunjunganBulanLalu > 0
            ? round((($kunjunganBulan - $kunjunganBulanLalu) / $kunjunganBulanLalu) * 100)
            : ($kunjunganBulan > 0 ? 100 : 0);

        $statusKunjungan = collect(KunjunganUks::STATUS)->map(fn ($status) => [
            'status' => $status,
            'total'  => $kunjunganPerStatus[$status] ?? 0,
            'persen' => $kunjunganBulan > 0 ? round((($kunjunganPerStatus[$status] ?? 0) / $kunjunganBulan) * 100) : 0,
        ]);

        // Tren 7 hari terakhir; jumlah hari ini diambil dari data yang sama.
        $kunjunganHarianMap = $kunjungan()
            ->whereBetween('tanggal', [$today->copy()->subDays(6)->toDateString(), $today->toDateString()])
            ->selectRaw('tanggal, count(*) as total')
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal')
            ->mapWithKeys(fn ($total, $tanggal) => [substr($tanggal, 0, 10) => (int) $total]);
        $kunjunganHarian = collect(range(6, 0))->map(function ($offset) use ($today, $kunjunganHarianMap) {
            $date = $today->copy()->subDays($offset);

            return [
                'label' => $date->translatedFormat('d M'),
                'date'  => $date->toDateString(),
                'total' => $kunjunganHarianMap[$date->toDateString()] ?? 0,
            ];
        });

        // Cakupan raport hanya menghitung siswa aktif agar sebanding dengan total siswa.
        $pemeriksaanSemester = $siswaAktif()->sudahDiperiksa($semesterBerjalan, $tahunAjaran)->count();
        $belumPemeriksaanSemester = max(0, $totalSiswa - $pemeriksaanSemester);

        return view('pages.dashboard', [
            'layout'                       => 'side-menu',
            'total_siswa'                  => $totalSiswa,
            'jenjang'                      => Jenjang::orderBy('nama')->get(),
            'kelasOptions'                 => Anggota::kelasOptions($request->input('jenjang_id')),
            'dashboardFilter'              => $filters,
            'kunjungan_bulan'              => $kunjunganBulan,
            'perubahan_kunjungan'          => $perubahanKunjungan,
            'kunjungan_hari_ini'           => $kunjunganHarianMap[$today->toDateString()] ?? 0,
            'kasus_prioritas'              => collect(KunjunganUks::STATUS_PRIORITAS)->sum(fn ($status) => $kunjunganPerStatus[$status] ?? 0),
            'status_kunjungan'             => $statusKunjungan,
            'kunjungan_harian'             => $kunjunganHarian,
            'total_trend_kunjungan'        => $kunjunganHarian->sum('total'),
            'semester_berjalan'            => $semesterBerjalan,
            'tahun_ajaran_berjalan'        => $tahunAjaran,
            'pemeriksaan_semester'         => $pemeriksaanSemester,
            'belum_pemeriksaan_semester'   => $belumPemeriksaanSemester,
            'cakupan_pemeriksaan_semester' => $totalSiswa > 0 ? round(($pemeriksaanSemester / $totalSiswa) * 100) : 0,
            'mcu_belum_list'               => $siswaAktif()
                ->select('id', 'jenjang_id', 'nis_nip', 'nama', 'kelas', 'tipe')
                ->with('jenjang:id,nama')
                ->belumDiperiksa($semesterBerjalan, $tahunAjaran)
                ->orderBy('nama')
                ->limit(5)
                ->get(),
            'kunjungan_terbaru'            => $kunjungan()
                ->with('anggota.jenjang')
                ->latest('tanggal')
                ->latest('jam')
                ->limit(6)
                ->get(),
        ]);
    }
}
