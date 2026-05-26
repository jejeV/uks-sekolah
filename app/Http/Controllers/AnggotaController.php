<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Jenjang;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    protected $layout = 'side-menu';

    public function index(Request $request)
    {
        $query = Anggota::with('jenjang');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nis_nip', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('tipe'))      $query->where('tipe', $request->tipe);
        if ($request->filled('jenjang_id')) $query->where('jenjang_id', $request->jenjang_id);

        return view('pages.anggota.index', [
            'layout'  => $this->layout,
            'anggota' => $query->orderBy('nama')->paginate(15)->withQueryString(),
            'jenjang' => Jenjang::all(),
        ]);
    }

    public function create()
    {
        return view('pages.anggota.create', [
            'layout'  => $this->layout,
            'jenjang' => Jenjang::all(),
        ]);
    }

    public function show(Anggota $anggota)
    {
        $anggota->load([
            'jenjang',
            'riwayatPenyakit' => fn ($query) => $query->latest('tgl_mulai'),
            'pemeriksaan' => fn ($query) => $query->with('petugas')->latest(),
            'kunjungan' => fn ($query) => $query->with('petugas')->latest('tanggal')->limit(8),
        ]);

        $semesterBerjalan = now()->month >= 7 ? 1 : 2;
        $tahunAjaran = now()->month >= 7 ? now()->year : now()->year - 1;
        $mcuSemesterIni = $anggota->pemeriksaan
            ->firstWhere('semester', $semesterBerjalan);
        $mcuSemesterIni = $mcuSemesterIni && (int) $mcuSemesterIni->tahun_ajaran === $tahunAjaran
            ? $mcuSemesterIni
            : null;
        $mcuTerakhir = $anggota->pemeriksaan->first();
        $riwayatAktif = $anggota->riwayatPenyakit
            ->whereIn('status', ['aktif', 'kronis'])
            ->count();

        return view('pages.anggota.show', [
            'layout'           => $this->layout,
            'anggota'          => $anggota,
            'semesterBerjalan' => $semesterBerjalan,
            'tahunAjaran'      => $tahunAjaran,
            'mcuSemesterIni'   => $mcuSemesterIni,
            'mcuTerakhir'      => $mcuTerakhir,
            'riwayatAktif'     => $riwayatAktif,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenjang_id'    => 'required|exists:jenjang,id',
            'nis_nip'       => 'required|string|unique:anggota,nis_nip',
            'nama'          => 'required|string|max:100',
            'tipe'          => 'required|in:siswa,guru,tenaga_kependidikan',
            'kelas'         => 'nullable|string|max:20',
            'tgl_lahir'     => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'redirect_to'    => 'nullable|in:dashboard,anggota.index',
        ]);

        Anggota::create($request->only([
            'jenjang_id',
            'nis_nip',
            'nama',
            'tipe',
            'kelas',
            'tgl_lahir',
            'jenis_kelamin',
        ]));

        return redirect()->route($request->input('redirect_to', 'anggota.index'))
                         ->with('success', 'Data anggota berhasil ditambahkan.');
    }

    public function edit(Anggota $anggota)
    {
        return view('pages.anggota.edit', [
            'layout'  => $this->layout,
            'anggota' => $anggota,
            'jenjang' => Jenjang::all(),
        ]);
    }

    public function update(Request $request, Anggota $anggota)
    {
        $request->validate([
            'jenjang_id'    => 'required|exists:jenjang,id',
            'nis_nip'       => 'required|string|unique:anggota,nis_nip,' . $anggota->id,
            'nama'          => 'required|string|max:100',
            'tipe'          => 'required|in:siswa,guru,tenaga_kependidikan',
            'kelas'         => 'nullable|string|max:20',
            'tgl_lahir'     => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        $anggota->update($request->only([
            'jenjang_id',
            'nis_nip',
            'nama',
            'tipe',
            'kelas',
            'tgl_lahir',
            'jenis_kelamin',
        ]));

        return redirect()->route('anggota.index')
                         ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota)
    {
        $anggota->delete();

        return redirect()->route('anggota.index')
                         ->with('success', 'Data anggota berhasil dihapus.');
    }
}
