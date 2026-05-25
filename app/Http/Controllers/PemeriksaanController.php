<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemeriksaanController extends Controller
{
    protected $layout = 'side-menu';

    public function index(Request $request)
    {
        $query = PemeriksaanKesehatan::with(['anggota.jenjang', 'petugas']);

        if ($request->filled('semester'))    $query->where('semester', $request->semester);
        if ($request->filled('tahun_ajaran')) $query->where('tahun_ajaran', $request->tahun_ajaran);
        if ($request->filled('search')) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        return view('pages.pemeriksaan.index', [
            'layout'       => $this->layout,
            'pemeriksaan'  => $query->latest()->paginate(15)->withQueryString(),
            'tahunOptions' => range(now()->year, now()->year - 5),
        ]);
    }

    public function create()
    {
        return view('pages.pemeriksaan.create', [
            'layout'       => $this->layout,
            'anggota'      => Anggota::where('aktif', true)->where('tipe', 'siswa')->orderBy('nama')->get(),
            'tahunOptions' => range(now()->year, now()->year - 5),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id'        => 'required|exists:anggota,id',
            'semester'          => 'required|in:1,2',
            'tahun_ajaran'      => 'required|digits:4',
            'berat_badan'       => 'nullable|numeric|min:1|max:200',
            'tinggi_badan'      => 'nullable|numeric|min:50|max:250',
            'penglihatan_kiri'  => 'nullable|string|max:10',
            'penglihatan_kanan' => 'nullable|string|max:10',
            'pendengaran'       => 'nullable|in:normal,kurang,tuli',
            'kondisi_gigi'      => 'nullable|in:baik,caries,perlu_perawatan',
            'catatan'           => 'nullable|string',
        ]);

        $bmi = null;
        if ($request->filled('berat_badan') && $request->filled('tinggi_badan')) {
            $tinggiMeter = $request->tinggi_badan / 100;
            $bmi = round($request->berat_badan / ($tinggiMeter * $tinggiMeter), 2);
        }

        PemeriksaanKesehatan::create([
            ...$request->except('_token'),
            'petugas_id' => Auth::id(),
            'bmi'        => $bmi,
        ]);

        return redirect()->route('pemeriksaan.index')
                         ->with('success', 'Data pemeriksaan berhasil disimpan.');
    }

    public function edit(PemeriksaanKesehatan $pemeriksaan)
    {
        return view('pages.pemeriksaan.edit', [
            'layout'       => $this->layout,
            'pemeriksaan'  => $pemeriksaan,
            'anggota'      => Anggota::where('aktif', true)->where('tipe', 'siswa')->orderBy('nama')->get(),
            'tahunOptions' => range(now()->year, now()->year - 5),
        ]);
    }

    public function update(Request $request, PemeriksaanKesehatan $pemeriksaan)
    {
        $request->validate([
            'anggota_id'        => 'required|exists:anggota,id',
            'semester'          => 'required|in:1,2',
            'tahun_ajaran'      => 'required|digits:4',
            'berat_badan'       => 'nullable|numeric|min:1|max:200',
            'tinggi_badan'      => 'nullable|numeric|min:50|max:250',
            'penglihatan_kiri'  => 'nullable|string|max:10',
            'penglihatan_kanan' => 'nullable|string|max:10',
            'pendengaran'       => 'nullable|in:normal,kurang,tuli',
            'kondisi_gigi'      => 'nullable|in:baik,caries,perlu_perawatan',
            'catatan'           => 'nullable|string',
        ]);

        $bmi = null;
        if ($request->filled('berat_badan') && $request->filled('tinggi_badan')) {
            $tinggiMeter = $request->tinggi_badan / 100;
            $bmi = round($request->berat_badan / ($tinggiMeter * $tinggiMeter), 2);
        }

        $pemeriksaan->update([
            ...$request->except('_token', '_method'),
            'bmi' => $bmi,
        ]);

        return redirect()->route('pemeriksaan.index')
                         ->with('success', 'Data pemeriksaan berhasil diperbarui.');
    }

    public function show(PemeriksaanKesehatan $pemeriksaan)
    {
        return view('pages.pemeriksaan.show', [
            'layout'      => $this->layout,
            'pemeriksaan' => $pemeriksaan->load(['anggota.jenjang', 'petugas']),
        ]);
    }

    public function destroy(PemeriksaanKesehatan $pemeriksaan)
    {
        $pemeriksaan->delete();

        return redirect()->route('pemeriksaan.index')
                         ->with('success', 'Data pemeriksaan berhasil dihapus.');
    }
}
