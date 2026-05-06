<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KunjunganExport;
use App\Exports\RiwayatExport;
use App\Exports\PemeriksaanExport;

class ExportController extends Controller
{
    public function kunjungan(Request $request)
    {
        return Excel::download(
            new KunjunganExport($request->all()),
            'riwayat-kunjungan-uks-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function riwayat(Request $request)
    {
        return Excel::download(
            new RiwayatExport($request->all()),
            'riwayat-penyakit-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function pemeriksaan(Request $request)
    {
        return Excel::download(
            new PemeriksaanExport($request->all()),
            'raport-kesehatan-' . now()->format('Ymd') . '.xlsx'
        );
    }
}
