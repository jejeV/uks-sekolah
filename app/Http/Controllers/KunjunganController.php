<?php
namespace App\Http\Controllers;

use App\Http\Requests\KunjunganRequest;
use App\Models\Anggota;
use App\Models\Jenjang;
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
        $query = KunjunganUks::with(['anggota.jenjang', 'petugas'])
            ->filterAnggota($request->only(['jenjang_id', 'kelas', 'search']))
            ->when($request->filled('tanggal'), fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->input('prioritas') === 'tinggi', fn ($q) => $q->whereIn('status', KunjunganUks::STATUS_PRIORITAS));

        return view('pages.kunjungan.index', [
            'layout'       => $this->layout,
            'kunjungan'    => $query->latest('tanggal')->latest('jam')->latest('id')->paginate(15)->withQueryString(),
            'jenjang'      => Jenjang::orderBy('nama')->get(),
            'kelasOptions' => Anggota::kelasOptions($request->input('jenjang_id')),
        ]);
    }

    public function store(KunjunganRequest $request)
    {
        $kunjungan = DB::transaction(function () use ($request) {
            $kunjungan = KunjunganUks::create([
                ...$request->kunjunganData(),
                'petugas_id' => Auth::id(),
            ]);

            if ($request->filled('nama_penyakit')) {
                RiwayatPenyakit::create([
                    'anggota_id'    => $kunjungan->anggota_id,
                    'kunjungan_id'  => $kunjungan->id,
                    'nama_penyakit' => $request->nama_penyakit,
                    'kode_icd'      => $request->kode_icd,
                    'tgl_mulai'     => $kunjungan->tanggal,
                    'status'        => 'aktif',
                ]);
            }

            return $kunjungan;
        });

        $redirectTo = $request->input('redirect_to', 'kunjungan.index');
        $parameters = $redirectTo === 'anggota.show' ? [$kunjungan->anggota_id] : [];

        return redirect()->route($redirectTo, $parameters)
                         ->with('success', 'Data kunjungan berhasil disimpan.');
    }

    public function update(KunjunganRequest $request, KunjunganUks $kunjungan)
    {
        $kunjungan->update($request->kunjunganData());

        return back()->with('success', 'Data kunjungan berhasil diperbarui.');
    }

    public function destroy(KunjunganUks $kunjungan)
    {
        $kunjungan->delete();

        return back()->with('success', 'Data kunjungan berhasil dihapus.');
    }
}
