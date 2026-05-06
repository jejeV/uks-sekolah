<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\KunjunganUks;
use App\Models\RiwayatPenyakit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KunjunganController extends Controller
{
    protected $layout = 'side-menu';

    public function index(Request $request)
    {
        $query = KunjunganUks::with(['anggota.jenjang', 'petugas']);

        if ($request->filled('search')) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('tanggal')) $query->whereDate('tanggal', $request->tanggal);
        if ($request->filled('status'))  $query->where('status', $request->status);

        return view('pages.kunjungan.index', [
            'layout'    => $this->layout,
            'kunjungan' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('pages.kunjungan.create', [
            'layout'  => $this->layout,
            'anggota' => Anggota::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id'    => 'required|exists:anggota,id',
            'tanggal'       => 'required|date',
            'jam'           => 'nullable|date_format:H:i',
            'keluhan'       => 'required|string',
            'diagnosis'     => 'nullable|string',
            'tindakan'      => 'nullable|string',
            'obat'          => 'nullable|string|max:200',
            'status'        => 'required|in:ringan,sedang,berat,dirujuk',
            'nama_penyakit' => 'nullable|string|max:100',
            'kode_icd'      => 'nullable|string|max:10',
        ]);

        DB::transaction(function () use ($request) {
            $kunjungan = KunjunganUks::create([
                ...$request->except('_token', 'nama_penyakit', 'kode_icd'),
                'petugas_id' => Auth::id(),
            ]);

            if ($request->filled('nama_penyakit')) {
                RiwayatPenyakit::create([
                    'anggota_id'    => $request->anggota_id,
                    'kunjungan_id'  => $kunjungan->id,
                    'nama_penyakit' => $request->nama_penyakit,
                    'kode_icd'      => $request->kode_icd,
                    'tgl_mulai'     => $request->tanggal,
                    'status'        => 'aktif',
                ]);
            }
        });

        return redirect()->route('kunjungan.index')
                         ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function show(KunjunganUks $kunjungan)
    {
        return view('pages.kunjungan.show', [
            'layout'    => $this->layout,
            'kunjungan' => $kunjungan->load(['anggota.jenjang', 'petugas', 'riwayatPenyakit']),
        ]);
    }

    public function destroy(KunjunganUks $kunjungan)
    {
        $kunjungan->delete();

        return redirect()->route('kunjungan.index')
                         ->with('success', 'Data kunjungan berhasil dihapus.');
    }
}
