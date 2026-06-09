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
        $trendStart = $today->copy()->subDays(6);
        $semesterBerjalan = $today->month >= 7 ? 1 : 2;
        $tahunAjaran = $today->month >= 7 ? $today->year : $today->year - 1;

        $totalSiswa = Anggota::where('aktif', true)
            ->where('tipe', 'siswa')
            ->count();

        $kunjunganBulanQuery = KunjunganUks::whereBetween('tanggal', [
            $monthStart->toDateString(),
            $monthEnd->toDateString(),
        ]);
        $kunjunganBulan = (clone $kunjunganBulanQuery)->count();
        $kunjunganBulanLalu = KunjunganUks::whereBetween('tanggal', [
            $lastMonthStart->toDateString(),
            $lastMonthEnd->toDateString(),
        ])->count();
        $perubahanKunjungan = $kunjunganBulanLalu > 0
            ? round((($kunjunganBulan - $kunjunganBulanLalu) / $kunjunganBulanLalu) * 100)
            : ($kunjunganBulan > 0 ? 100 : 0);
        $kunjunganPerStatus = (clone $kunjunganBulanQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusKunjungan = collect(['ringan', 'sedang', 'berat', 'dirujuk'])
            ->map(function ($status) use ($kunjunganPerStatus, $kunjunganBulan) {
                $total = (int) ($kunjunganPerStatus[$status] ?? 0);

                return [
                    'status' => $status,
                    'total' => $total,
                    'persen' => $kunjunganBulan > 0
                        ? round(($total / $kunjunganBulan) * 100)
                        : 0,
                ];
            });

        $kunjunganHarianMap = KunjunganUks::selectRaw('tanggal, count(*) as total')
            ->whereBetween('tanggal', [$trendStart->toDateString(), $today->toDateString()])
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');
        $kunjunganHarian = collect(range(6, 0))
            ->map(function ($offset) use ($today, $kunjunganHarianMap) {
                $date = $today->copy()->subDays($offset);

                return [
                    'label' => $date->translatedFormat('d M'),
                    'date' => $date->toDateString(),
                    'total' => (int) ($kunjunganHarianMap[$date->toDateString()] ?? 0),
                ];
            });

        $pemeriksaanSemester = PemeriksaanKesehatan::where('semester', $semesterBerjalan)
            ->where('tahun_ajaran', $tahunAjaran)
            ->distinct('anggota_id')
            ->count('anggota_id');
        $belumPemeriksaanSemester = max(0, $totalSiswa - $pemeriksaanSemester);
        $cakupanPemeriksaanSemester = $totalSiswa > 0
            ? round(($pemeriksaanSemester / $totalSiswa) * 100)
            : 0;

        $mcuBelumList = Anggota::select('id', 'jenjang_id', 'nama', 'kelas')
            ->with('jenjang:id,nama')
            ->where('aktif', true)
            ->where('tipe', 'siswa')
            ->whereDoesntHave('pemeriksaan', function ($query) use ($semesterBerjalan, $tahunAjaran) {
                $query->where('semester', $semesterBerjalan)
                    ->where('tahun_ajaran', $tahunAjaran);
            })
            ->orderBy('nama')
            ->limit(5)
            ->get();

        return view('pages.dashboard', [
            'layout' => 'side-menu',
            'total_siswa' => $totalSiswa,
            'jenjang' => Jenjang::orderBy('nama')->get(),
            'kunjungan_bulan' => $kunjunganBulan,
            'perubahan_kunjungan' => $perubahanKunjungan,
            'kunjungan_hari_ini' => KunjunganUks::whereDate('tanggal', $today->toDateString())->count(),
            'kunjungan_per_status' => $kunjunganPerStatus,
            'status_kunjungan' => $statusKunjungan,
            'kunjungan_harian' => $kunjunganHarian,
            'total_trend_kunjungan' => $kunjunganHarian->sum('total'),
            'semester_berjalan' => $semesterBerjalan,
            'tahun_ajaran_berjalan' => $tahunAjaran,
            'pemeriksaan_semester' => $pemeriksaanSemester,
            'belum_pemeriksaan_semester' => $belumPemeriksaanSemester,
            'cakupan_pemeriksaan_semester' => $cakupanPemeriksaanSemester,
            'mcu_belum_list' => $mcuBelumList,
            'kunjungan_terbaru' => KunjunganUks::with('anggota.jenjang')
                ->latest('tanggal')
                ->latest('jam')
                ->limit(6)
                ->get(),
        ]);
    }

    public function anggotaOptions(Request $request)
    {
        $query = Anggota::select('id', 'jenjang_id', 'nis_nip', 'nama', 'tipe', 'kelas')
            ->with('jenjang:id,nama')
            ->where('aktif', true);

        if ($request->input('tipe') === 'siswa') {
            $query->where('tipe', 'siswa');
        }

        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', '%' . $keyword . '%')
                    ->orWhere('nis_nip', 'like', '%' . $keyword . '%');
            });
        }

        return $query->orderBy('nama')
            ->limit(20)
            ->get()
            ->map(function ($anggota) {
                return [
                    'id' => $anggota->id,
                    'label' => $anggota->nama . ' (' . ucfirst(str_replace('_', ' ', $anggota->tipe)) . ' - ' . (optional($anggota->jenjang)->nama ?? '-') . ')',
                ];
            });
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
