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
        ]);

        Anggota::create($request->all());

        return redirect()->route('anggota.index')
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

        $anggota->update($request->all());

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
