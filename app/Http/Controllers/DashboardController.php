<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\KunjunganUks;
use App\Models\Jenjang;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $lastMonthStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $today->copy()->subMonthNoOverflow()->endOfMonth();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $trendStart = $today->copy()->subDays(6)->startOfDay();
        $semesterBerjalan = $today->month >= 7 ? 1 : 2;
        $tahunAjaran = $today->month >= 7 ? $today->year : $today->year - 1;

        $anggotaPerTipe = Anggota::selectRaw('tipe, count(*) as total')
            ->where('aktif', true)
            ->groupBy('tipe')
            ->pluck('total', 'tipe');
        $totalSiswa = (int) ($anggotaPerTipe['siswa'] ?? 0);
        $totalGuruStaff = (int) ($anggotaPerTipe['guru'] ?? 0) + (int) ($anggotaPerTipe['tenaga_kependidikan'] ?? 0);

        $kunjunganBulan = KunjunganUks::whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])->count();
        $kunjunganBulanLalu = KunjunganUks::whereBetween('tanggal', [$lastMonthStart->toDateString(), $lastMonthEnd->toDateString()])->count();
        $kunjunganHariIni = KunjunganUks::whereDate('tanggal', $today->toDateString())->count();
        $kunjunganMingguIni = KunjunganUks::whereBetween('tanggal', [$weekStart->toDateString(), $weekEnd->toDateString()])->count();
        $kunjunganHarianMap = KunjunganUks::selectRaw('tanggal, count(*) as total')
            ->whereBetween('tanggal', [$trendStart->toDateString(), $today->toDateString()])
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');
        $kunjunganHarian = collect(range(6, 0))->map(function ($day) use ($today, $kunjunganHarianMap) {
            $date = $today->copy()->subDays($day);

            return [
                'label' => $date->translatedFormat('d M'),
                'date' => $date->toDateString(),
                'total' => (int) ($kunjunganHarianMap[$date->toDateString()] ?? 0),
            ];
        });
        $kunjunganTrendList = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->whereBetween('tanggal', [$trendStart->toDateString(), $today->toDateString()])
            ->latest('tanggal')
            ->latest('jam')
            ->get();
        $kunjunganTrendByDate = $kunjunganTrendList->groupBy(fn ($item) => $item->tanggal->toDateString());
        $hariTersibuk = $kunjunganHarian
            ->sortByDesc('total')
            ->first();
        $totalTrendKunjungan = $kunjunganHarian->sum('total');
        $rataKunjunganHarian = round($kunjunganHarian->avg('total'), 1);

        $kunjunganPerStatus = KunjunganUks::selectRaw('status, count(*) as total')
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('status')
            ->pluck('total', 'status');
        $kunjunganBulanList = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->latest('tanggal')
            ->latest('jam')
            ->get();
        $kunjunganStatusDetail = $kunjunganBulanList->groupBy('status');
        $statusKunjungan = collect(['ringan', 'sedang', 'berat', 'dirujuk'])->map(function ($status) use ($kunjunganPerStatus, $kunjunganBulan) {
            $total = (int) ($kunjunganPerStatus[$status] ?? 0);

            return [
                'status' => $status,
                'total' => $total,
                'persen' => $kunjunganBulan > 0 ? round(($total / $kunjunganBulan) * 100) : 0,
            ];
        });
        $statusTerbanyak = $statusKunjungan
            ->sortByDesc('total')
            ->first();

        $jenjangStat = Jenjang::withCount([
                'anggota as total_aktif' => fn ($query) => $query->where('aktif', true),
                'anggota as total_siswa' => fn ($query) => $query->where('aktif', true)->where('tipe', 'siswa'),
            ])
            ->orderBy('nama')
            ->get();
        $pemeriksaanTahunIni = PemeriksaanKesehatan::whereYear('created_at', $today->year)
            ->distinct('anggota_id')
            ->count('anggota_id');
        $belumPemeriksaanTahunIni = max(0, $totalSiswa - $pemeriksaanTahunIni);
        $cakupanPemeriksaan = $totalSiswa > 0 ? round(($pemeriksaanTahunIni / $totalSiswa) * 100) : 0;
        $pemeriksaanSemesterIni = PemeriksaanKesehatan::where('semester', $semesterBerjalan)
            ->where('tahun_ajaran', $tahunAjaran)
            ->distinct('anggota_id')
            ->count('anggota_id');
        $belumPemeriksaanSemesterIni = max(0, $totalSiswa - $pemeriksaanSemesterIni);
        $cakupanPemeriksaanSemester = $totalSiswa > 0 ? round(($pemeriksaanSemesterIni / $totalSiswa) * 100) : 0;
        $pemeriksaanBulan = PemeriksaanKesehatan::whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $anggotaAktif = Anggota::with('jenjang')->where('aktif', true)->orderBy('nama')->get();
        $pemeriksaanAnggota = $anggotaAktif->where('tipe', 'siswa')->values();
        $anggotaTipeDetail = $anggotaAktif->groupBy('tipe');
        $anggotaPetugasDetail = $anggotaAktif
            ->whereIn('tipe', ['guru', 'tenaga_kependidikan'])
            ->values();
        $anggotaJenjangDetail = $anggotaAktif->groupBy('jenjang_id');
        $mcuBelumList = Anggota::with('jenjang')
            ->where('aktif', true)
            ->where('tipe', 'siswa')
            ->whereDoesntHave('pemeriksaan', function ($query) use ($semesterBerjalan, $tahunAjaran) {
                $query->where('semester', $semesterBerjalan)
                    ->where('tahun_ajaran', $tahunAjaran);
            })
            ->orderBy('nama')
            ->limit(8)
            ->get();
        $mcuTerbaru = PemeriksaanKesehatan::with(['anggota.jenjang', 'petugas'])
            ->latest()
            ->limit(5)
            ->get();
        $mcuBulanList = PemeriksaanKesehatan::with(['anggota.jenjang', 'petugas'])
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->latest()
            ->get();
        $kunjunganHariIniList = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->whereDate('tanggal', $today->toDateString())
            ->latest()
            ->get();
        $kunjunganPrioritasList = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereIn('status', ['berat', 'dirujuk'])
            ->latest()
            ->get();
        $tipeAnggotaStat = collect([
            [
                'label' => 'Siswa',
                'tipe' => 'siswa',
                'total' => (int) ($anggotaPerTipe['siswa'] ?? 0),
            ],
            [
                'label' => 'Guru',
                'tipe' => 'guru',
                'total' => (int) ($anggotaPerTipe['guru'] ?? 0),
            ],
            [
                'label' => 'Staff',
                'tipe' => 'tenaga_kependidikan',
                'total' => (int) ($anggotaPerTipe['tenaga_kependidikan'] ?? 0),
            ],
        ]);

        return view('pages.dashboard', [
            'layout'               => 'side-menu',
            'total_siswa'          => $totalSiswa,
            'total_guru'           => $totalGuruStaff,
            'total_pemeriksaan'    => PemeriksaanKesehatan::count(),
            'jenjang'              => Jenjang::all(),
            'kunjungan_bulan'      => $kunjunganBulan,
            'kunjungan_bulan_lalu' => $kunjunganBulanLalu,
            'kunjungan_hari_ini'   => $kunjunganHariIni,
            'kunjungan_minggu_ini' => $kunjunganMingguIni,
            'pemeriksaan_bulan'    => $pemeriksaanBulan,
            'pemeriksaan_tahun_ini'=> $pemeriksaanTahunIni,
            'belum_pemeriksaan'    => $belumPemeriksaanTahunIni,
            'cakupan_pemeriksaan'  => $cakupanPemeriksaan,
            'semester_berjalan'    => $semesterBerjalan,
            'tahun_ajaran_berjalan'=> $tahunAjaran,
            'pemeriksaan_semester' => $pemeriksaanSemesterIni,
            'belum_pemeriksaan_semester' => $belumPemeriksaanSemesterIni,
            'cakupan_pemeriksaan_semester' => $cakupanPemeriksaanSemester,
            'mcu_belum_list'       => $mcuBelumList,
            'mcu_terbaru'          => $mcuTerbaru,
            'mcu_bulan_list'       => $mcuBulanList,
            'kunjungan_hari_ini_list' => $kunjunganHariIniList,
            'kunjungan_prioritas_list' => $kunjunganPrioritasList,
            'kunjungan_bulan_list' => $kunjunganBulanList,
            'kunjungan_trend_list' => $kunjunganTrendList,
            'kunjungan_trend_by_date' => $kunjunganTrendByDate,
            'kunjungan_status_detail' => $kunjunganStatusDetail,
            'anggota_per_tipe'     => $anggotaPerTipe,
            'tipe_anggota_stat'    => $tipeAnggotaStat,
            'anggota_tipe_detail'  => $anggotaTipeDetail,
            'anggota_petugas_detail' => $anggotaPetugasDetail,
            'anggota_jenjang_detail' => $anggotaJenjangDetail,
            'jenjang_stat'         => $jenjangStat,
            'kunjungan_harian'     => $kunjunganHarian,
            'hari_tersibuk'        => $hariTersibuk,
            'total_trend_kunjungan'=> $totalTrendKunjungan,
            'rata_kunjungan_harian'=> $rataKunjunganHarian,
            'status_kunjungan'     => $statusKunjungan,
            'status_terbanyak'     => $statusTerbanyak,
            'anggota_aktif'        => $anggotaAktif,
            'pemeriksaan_anggota'  => $pemeriksaanAnggota,
            'kunjungan_terbaru'    => KunjunganUks::with(['anggota.jenjang', 'petugas'])
                                        ->latest()->paginate(10),
            'kunjungan_per_status' => $kunjunganPerStatus,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'tanggal'    => 'required|date',
            'jam'        => 'nullable|date_format:H:i',
            'keluhan'    => 'required|string',
            'diagnosis'  => 'nullable|string',
            'tindakan'   => 'nullable|string',
            'obat'       => 'nullable|string|max:200',
            'status'     => 'required|in:ringan,sedang,berat,dirujuk',
        ]);

        DB::transaction(function () use ($request) {
            KunjunganUks::create([
                ...$request->except('_token', 'redirect_to'),
                'petugas_id' => Auth::id(),
            ]);
        });

        return redirect()->route('dashboard')
                         ->with('success', 'Data kunjungan berhasil ditambahkan.');
    }

    public function update(Request $request, KunjunganUks $kunjungan)
    {
        $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'tanggal'    => 'required|date',
            'jam'        => 'nullable|date_format:H:i',
            'keluhan'    => 'required|string',
            'diagnosis'  => 'nullable|string',
            'tindakan'   => 'nullable|string',
            'obat'       => 'nullable|string|max:200',
            'status'     => 'required|in:ringan,sedang,berat,dirujuk',
        ]);

        $kunjungan->update($request->except('_token', '_method', 'redirect_to'));

        return redirect()->route('dashboard')
                         ->with('success', 'Data kunjungan berhasil diperbarui.');
    }

    public function destroy(KunjunganUks $kunjungan)
    {
        $kunjungan->delete();

        return redirect()->route('dashboard')
                         ->with('success', 'Data kunjungan berhasil dihapus.');
    }
}
