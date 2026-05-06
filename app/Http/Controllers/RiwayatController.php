<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\RiwayatPenyakit;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    protected $layout = 'side-menu';

    public function index(Request $request)
    {
        $query = RiwayatPenyakit::with(['anggota.jenjang', 'kunjungan']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('anggota', function ($q2) use ($request) {
                    $q2->where('nama', 'like', '%' . $request->search . '%');
                })->orWhere('nama_penyakit', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) $query->where('status', $request->status);

        return view('pages.riwayat.index', [
            'layout'  => $this->layout,
            'riwayat' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Anggota $anggota)
    {
        return view('pages.riwayat.show', [
            'layout'    => $this->layout,
            'anggota'   => $anggota,
            'riwayat'   => RiwayatPenyakit::where('anggota_id', $anggota->id)
                            ->with('kunjungan')->latest()->get(),
            'kunjungan' => $anggota->kunjungan()->with('petugas')->latest()->get(),
        ]);
    }
}
