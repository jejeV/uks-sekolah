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
        $rows = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('anggota', fn ($anggota) => $anggota->where('nama', 'like', '%' . $request->search . '%'));
            })
            ->latest('tanggal')
            ->get()
            ->map(fn (KunjunganUks $item) => [
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
            $rows
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
        $rows = PemeriksaanKesehatan::with(['anggota.jenjang', 'petugas'])
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
                'Catatan' => $item->catatan ?: '-',
                'Petugas' => optional($item->petugas)->name ?? '-',
            ]);

        return $this->download(
            $format,
            'Raport Kesehatan',
            'raport-kesehatan',
            $rows
        );
    }

    private function download(string $format, string $title, string $filename, Collection $rows)
    {
        $format = $format ?: 'excel';
        $filename = $filename . '-' . now()->format('Ymd-His');
        $html = view('exports.table', [
            'title' => $title,
            'rows' => $rows,
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
