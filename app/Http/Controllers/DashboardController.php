<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\KunjunganUks;
use App\Models\Jenjang;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard', [
            'layout'               => 'side-menu',
            'total_siswa'          => Anggota::where('tipe', 'siswa')->where('aktif', true)->count(),
            'total_guru'           => Anggota::where('tipe', 'guru')->where('aktif', true)->count(),
            'total_pemeriksaan'    => PemeriksaanKesehatan::count(),
            'jenjang'              => Jenjang::all(),
            'kunjungan_bulan'      => KunjunganUks::whereMonth('tanggal', now()->month)
                                        ->whereYear('tanggal', now()->year)->count(),
            'pemeriksaan_anggota'  => Anggota::where('aktif', true)->where('tipe', 'siswa')->orderBy('nama')->get(),
            'kunjungan_terbaru'    => KunjunganUks::with(['anggota.jenjang', 'petugas'])
                                        ->latest()->paginate(10),
            'kunjungan_per_status' => KunjunganUks::selectRaw('status, count(*) as total')
                                        ->whereMonth('tanggal', now()->month)
                                        ->groupBy('status')->get()
                                        ->pluck('total', 'status'),
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

        KunjunganUks::create([
            ...$request->except('_token'),
            'petugas_id' => Auth::id(),
        ]);

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

        $kunjungan->update($request->except('_token', '_method'));

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
