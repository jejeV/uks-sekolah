<?php
namespace App\Http\Controllers;

use App\Models\KunjunganUks;
use App\Models\PemeriksaanKesehatan;
use App\Models\RiwayatPenyakit;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ExportController extends Controller
{
    public function kunjungan(Request $request, string $format = 'excel')
    {
        $kunjungan = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->when($request->filled('jenjang_id'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('jenjang_id', $request->jenjang_id));
            })
            ->when($request->filled('kelas'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('kelas', $request->kelas));
            })
            ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('nama', 'like', '%' . $request->search . '%'));
            })
            ->latest('tanggal')
            ->get();

        $rows = $kunjungan->map(fn (KunjunganUks $item) => [
                'Nama' => optional($item->anggota)->nama ?? '-',
                'Tipe' => ucfirst(str_replace('_', ' ', optional($item->anggota)->tipe ?? '-')),
                'Jenjang' => optional(optional($item->anggota)->jenjang)->nama ?? '-',
                'Tanggal' => optional($item->tanggal)->format('d/m/Y') ?? '-',
                'Jam' => $item->jam ?: '-',
                'Keluhan' => $item->keluhan,
                'Diagnosis' => $item->diagnosis ?: '-',
                'Tindakan' => $item->tindakan ?: '-',
                'Obat' => $item->obat ?: '-',
                'Status' => ucfirst($item->status),
                'Petugas' => optional($item->petugas)->name ?? '-',
            ]);

        return $this->download(
            $format,
            'Riwayat Kunjungan UKS',
            'riwayat-kunjungan-uks',
            $rows,
            $this->kunjunganSummary($kunjungan)
        );
    }

    public function riwayat(Request $request, string $format = 'excel')
    {
        $rows = RiwayatPenyakit::with(['anggota.jenjang', 'kunjungan'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('nama_penyakit', 'like', '%' . $request->search . '%')
                        ->orWhereHas('anggota', fn ($anggota) => $anggota->where('nama', 'like', '%' . $request->search . '%'));
                });
            })
            ->latest('tgl_mulai')
            ->get()
            ->map(fn (RiwayatPenyakit $item) => [
                'Nama' => optional($item->anggota)->nama ?? '-',
                'Tipe' => ucfirst(str_replace('_', ' ', optional($item->anggota)->tipe ?? '-')),
                'Jenjang' => optional(optional($item->anggota)->jenjang)->nama ?? '-',
                'Penyakit' => $item->nama_penyakit,
                'Kode ICD' => $item->kode_icd ?: '-',
                'Tanggal Mulai' => optional($item->tgl_mulai)->format('d/m/Y') ?? '-',
                'Tanggal Sembuh' => optional($item->tgl_sembuh)->format('d/m/Y') ?? '-',
                'Status' => ucfirst($item->status),
                'Kunjungan Terkait' => $item->kunjungan ? $item->kunjungan->tanggal->format('d/m/Y') : '-',
            ]);

        return $this->download(
            $format,
            'Riwayat Penyakit',
            'riwayat-penyakit',
            $rows
        );
    }

    public function pemeriksaan(Request $request, string $format = 'excel')
    {
        $periodeLabel = null;
        $filenameSuffix = null;
        $periode = $request->input('periode');
        $tanggal = $request->date('tanggal');
        $tanggalMulai = $request->date('tanggal_mulai');
        $tanggalSelesai = $request->date('tanggal_selesai');

        $rows = PemeriksaanKesehatan::with(['anggota.jenjang', 'petugas'])
            ->when($request->filled('jenjang_id'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('jenjang_id', $request->jenjang_id));
            })
            ->when($request->filled('kelas'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('kelas', $request->kelas));
            })
            ->when($tanggal, function ($query) use ($tanggal, &$periodeLabel, &$filenameSuffix) {
                $periodeLabel = 'Tanggal ' . $tanggal->translatedFormat('d F Y');
                $filenameSuffix = 'tanggal-' . $tanggal->toDateString();

                $query->whereDate('created_at', $tanggal->toDateString());
            })
            ->when($tanggalMulai, function ($query) use ($tanggalMulai, &$periodeLabel, &$filenameSuffix) {
                $periodeLabel = 'Periode Tanggal';
                $filenameSuffix = 'periode-tanggal';

                $query->whereDate('created_at', '>=', $tanggalMulai->toDateString());
            })
            ->when($tanggalSelesai, function ($query) use ($tanggalSelesai, &$periodeLabel, &$filenameSuffix) {
                $periodeLabel = 'Periode Tanggal';
                $filenameSuffix = 'periode-tanggal';

                $query->whereDate('created_at', '<=', $tanggalSelesai->toDateString());
            })
            ->when($periode === 'bulan_ini', function ($query) use (&$periodeLabel, &$filenameSuffix) {
                $periodeLabel = 'Bulan Ini';
                $filenameSuffix = 'bulan-ini';

                $query->whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]);
            })
            ->when($periode === 'semester_ini', function ($query) use (&$periodeLabel, &$filenameSuffix) {
                $periodeLabel = 'Semester Ini';
                $filenameSuffix = 'semester-ini';

                $query->where('semester', now()->month >= 7 ? 1 : 2)
                    ->where('tahun_ajaran', now()->month >= 7 ? now()->year : now()->year - 1);
            })
            ->when($periode === 'tahun_ajaran_ini', function ($query) use (&$periodeLabel, &$filenameSuffix) {
                $periodeLabel = 'Tahun Ajaran Ini';
                $filenameSuffix = 'tahun-ajaran-ini';

                $query->where('tahun_ajaran', now()->month >= 7 ? now()->year : now()->year - 1);
            })
            ->when($request->filled('semester'), fn ($query) => $query->where('semester', $request->semester))
            ->when($request->filled('tahun_ajaran'), fn ($query) => $query->where('tahun_ajaran', $request->tahun_ajaran))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('nama', 'like', '%' . $request->search . '%'));
            })
            ->latest()
            ->get()
            ->map(fn (PemeriksaanKesehatan $item) => [
                'Nama' => optional($item->anggota)->nama ?? '-',
                'Jenjang' => optional(optional($item->anggota)->jenjang)->nama ?? '-',
                'Kelas' => optional($item->anggota)->kelas ?: '-',
                'Semester' => $item->semester,
                'Tahun Ajaran' => $item->tahun_ajaran,
                'Berat Badan' => $item->berat_badan ? $item->berat_badan . ' kg' : '-',
                'Tinggi Badan' => $item->tinggi_badan ? $item->tinggi_badan . ' cm' : '-',
                'BMI' => $item->bmi ?: '-',
                'Penglihatan Kiri' => $item->penglihatan_kiri ?: '-',
                'Penglihatan Kanan' => $item->penglihatan_kanan ?: '-',
                'Pendengaran' => $item->pendengaran ? ucfirst($item->pendengaran) : '-',
                'Kondisi Gigi' => $item->kondisi_gigi ? ucfirst(str_replace('_', ' ', $item->kondisi_gigi)) : '-',
                'Gula Darah' => optional($item->anggota)->tipe === 'guru' && $item->gula_darah ? $item->gula_darah . ' mg/dL' : '-',
                'Kolesterol' => optional($item->anggota)->tipe === 'guru' && $item->kolesterol ? $item->kolesterol . ' mg/dL' : '-',
                'Catatan' => $item->catatan ?: '-',
                'Petugas' => optional($item->petugas)->name ?? '-',
            ]);

        return $this->download(
            $format,
            $periodeLabel ? 'Raport Kesehatan - ' . $periodeLabel : 'Raport Kesehatan',
            $filenameSuffix ? 'raport-kesehatan-' . $filenameSuffix : 'raport-kesehatan',
            $rows
        );
    }

    private function kunjunganSummary(Collection $kunjungan): array
    {
        $statusCounts = $kunjungan->countBy('status');
        $genderCounts = $kunjungan->countBy(fn (KunjunganUks $item) => optional($item->anggota)->jenis_kelamin ?: '-');
        $tipeCounts = $kunjungan->countBy(fn (KunjunganUks $item) => optional($item->anggota)->tipe ?: '-');
        $diagnosisCounts = $kunjungan
            ->pluck('diagnosis')
            ->filter()
            ->map(fn ($value) => trim(strtolower($value)))
            ->countBy()
            ->sortDesc()
            ->take(5);
        $keluhanCounts = $kunjungan
            ->pluck('keluhan')
            ->filter()
            ->map(fn ($value) => trim(strtolower($value)))
            ->countBy()
            ->sortDesc()
            ->take(5);
        $tanggalAwal = $kunjungan->min(fn (KunjunganUks $item) => optional($item->tanggal)->toDateString());
        $tanggalAkhir = $kunjungan->max(fn (KunjunganUks $item) => optional($item->tanggal)->toDateString());

        return [
            [
                'title' => 'Ringkasan Kunjungan',
                'items' => [
                    'Total Kunjungan' => $kunjungan->count(),
                    'Anggota Unik' => $kunjungan->pluck('anggota_id')->unique()->count(),
                    'Petugas Terlibat' => $kunjungan->pluck('petugas_id')->unique()->count(),
                    'Tanggal Awal' => $tanggalAwal ? \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') : '-',
                    'Tanggal Akhir' => $tanggalAkhir ? \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') : '-',
                    'Ringan' => (int) ($statusCounts['ringan'] ?? 0),
                    'Sedang' => (int) ($statusCounts['sedang'] ?? 0),
                    'Berat' => (int) ($statusCounts['berat'] ?? 0),
                    'Dirujuk' => (int) ($statusCounts['dirujuk'] ?? 0),
                    'Diberi Obat' => $kunjungan->filter(fn (KunjunganUks $item) => filled($item->obat))->count(),
                    'Ada Diagnosis' => $kunjungan->filter(fn (KunjunganUks $item) => filled($item->diagnosis))->count(),
                ],
            ],
            [
                'title' => 'Berdasarkan Jenis Kelamin',
                'items' => [
                    'Laki-laki' => (int) ($genderCounts['L'] ?? 0),
                    'Perempuan' => (int) ($genderCounts['P'] ?? 0),
                    'Tidak Terdata' => (int) ($genderCounts['-'] ?? 0),
                ],
            ],
            [
                'title' => 'Berdasarkan Tipe Anggota',
                'items' => [
                    'Siswa' => (int) ($tipeCounts['siswa'] ?? 0),
                    'Guru' => (int) ($tipeCounts['guru'] ?? 0),
                    'Tenaga Kependidikan' => (int) ($tipeCounts['tenaga_kependidikan'] ?? 0),
                    'Tidak Terdata' => (int) ($tipeCounts['-'] ?? 0),
                ],
            ],
            [
                'title' => 'Diagnosis Terbanyak',
                'items' => $diagnosisCounts->isNotEmpty()
                    ? $diagnosisCounts
                        ->mapWithKeys(fn ($total, $diagnosis) => [ucfirst($diagnosis) => $total])
                        ->all()
                    : ['Belum Ada Diagnosis' => 0],
            ],
            [
                'title' => 'Keluhan Terbanyak',
                'items' => $keluhanCounts->isNotEmpty()
                    ? $keluhanCounts
                        ->mapWithKeys(fn ($total, $keluhan) => [ucfirst($keluhan) => $total])
                        ->all()
                    : ['Belum Ada Keluhan' => 0],
            ],
        ];
    }

    private function download(string $format, string $title, string $filename, Collection $rows, array $summary = [])
    {
        $format = $format ?: 'excel';
        $filename = $filename . '-' . now()->format('Ymd-His');
        $html = view('exports.table', [
            'title' => $title,
            'rows' => $rows,
            'summary' => $summary,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->render();

        if ($format === 'pdf') {
            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);

            $pdf = new Dompdf($options);
            $pdf->loadHtml($html);
            $pdf->setPaper('a4', 'landscape');
            $pdf->render();

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
