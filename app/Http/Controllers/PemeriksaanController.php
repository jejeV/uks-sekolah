<?php
namespace App\Http\Controllers;

use App\Http\Requests\PemeriksaanRequest;
use App\Models\Anggota;
use App\Models\Jenjang;
use App\Models\PemeriksaanKesehatan;
use App\Support\TahunAjaran;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PemeriksaanController extends Controller
{
    protected $layout = 'side-menu';

    public function index(Request $request)
    {
        $filters = $request->only(['jenjang_id', 'kelas', 'search']);
        $statusPemeriksaan = in_array($request->input('status_pemeriksaan'), ['sudah', 'belum'], true) ? $request->status_pemeriksaan : null;
        $tipeAnggota = in_array($request->input('tipe_anggota'), ['siswa', 'guru'], true) ? $request->tipe_anggota : null;
        $semester = in_array((int) $request->input('semester'), [1, 2], true) ? (int) $request->semester : TahunAjaran::semester();
        $tahunAjaran = $request->filled('tahun_ajaran') ? (int) $request->tahun_ajaran : TahunAjaran::tahun();

        $anggotaPeriksa = fn () => Anggota::aktif()->whereIn('anggota.tipe', ['siswa', 'guru'])->filter($filters);

        // Rekap sudah/belum per tipe dalam satu query agregat.
        $rekap = $anggotaPeriksa()
            ->selectRaw('tipe, COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM pemeriksaan_kesehatan pk
                    WHERE pk.anggota_id = anggota.id AND pk.semester = ? AND pk.tahun_ajaran = ?
                ) THEN 1 ELSE 0 END) as sudah', [$semester, $tahunAjaran])
            ->groupBy('tipe')
            ->toBase()
            ->get()
            ->keyBy('tipe');

        $sudah = fn ($tipe) => (int) optional($rekap->get($tipe))->sudah;
        $belum = fn ($tipe) => (int) optional($rekap->get($tipe))->total - $sudah($tipe);

        $ringkasan = [
            'total'                 => $sudah('siswa') + $sudah('guru'),
            'siswa_diperiksa'       => $sudah('siswa'),
            'guru_diperiksa'        => $sudah('guru'),
            'siswa_belum_diperiksa' => $belum('siswa'),
            'guru_belum_diperiksa'  => $belum('guru'),
            'perlu_tindak_lanjut'   => PemeriksaanKesehatan::periode($semester, $tahunAjaran)
                ->perluTindakLanjut()
                ->whereHas('anggota', fn ($q) => $q->filter($filters))
                ->count(),
        ];

        $daftarRaport = $anggotaPeriksa()
            ->with([
                'jenjang',
                'pemeriksaan' => fn ($q) => $q->periode($semester, $tahunAjaran),
            ])
            ->when($tipeAnggota, fn ($q) => $q->where('anggota.tipe', $tipeAnggota))
            ->when($statusPemeriksaan === 'sudah', fn ($q) => $q->sudahDiperiksa($semester, $tahunAjaran))
            ->when($statusPemeriksaan === 'belum', fn ($q) => $q->belumDiperiksa($semester, $tahunAjaran))
            ->urutJenjang()
            ->paginate(15)
            ->withQueryString();

        return view('pages.pemeriksaan.index', [
            'layout'               => $this->layout,
            'daftarRaport'         => $daftarRaport,
            'statusPemeriksaan'    => $statusPemeriksaan,
            'tipeAnggota'          => $tipeAnggota,
            'semesterRingkasan'    => $semester,
            'tahunAjaranRingkasan' => $tahunAjaran,
            'isPeriodeBerjalan'    => $semester === TahunAjaran::semester() && $tahunAjaran === TahunAjaran::tahun(),
            'ringkasan'            => $ringkasan,
            'tahunOptions'         => TahunAjaran::options(),
            'jenjang'              => Jenjang::orderBy('nama')->get(),
            'kelasOptions'         => Anggota::kelasOptions($request->input('jenjang_id')),
        ]);
    }

    public function create(Request $request)
    {
        $anggota = $request->filled('anggota_id') ? Anggota::with('jenjang')->find($request->anggota_id) : null;

        return view('pages.pemeriksaan.create', [
            'layout'          => $this->layout,
            'selectedAnggota' => $anggota,
        ]);
    }

    public function store(PemeriksaanRequest $request)
    {
        $semesterBerjalan = TahunAjaran::semester();
        $tahunAjaranBerjalan = TahunAjaran::tahun();

        if ((int) $request->semester !== $semesterBerjalan || (int) $request->tahun_ajaran !== $tahunAjaranBerjalan) {
            return back()->withInput()->withErrors([
                'semester' => 'Input raport kesehatan hanya dapat dilakukan pada Semester ' . $semesterBerjalan . ' tahun ajaran ' . $tahunAjaranBerjalan . ' (Semester 1: Juli-Desember; Semester 2: Januari-Juni).',
            ]);
        }

        $anggota = Anggota::findOrFail($request->anggota_id);

        if ($this->sudahAdaPemeriksaan($anggota->id, $semesterBerjalan, $tahunAjaranBerjalan)) {
            return back()->withInput()->withErrors([
                'semester' => "Raport kesehatan {$anggota->nama} untuk semester dan tahun ajaran ini sudah tercatat.",
            ]);
        }

        PemeriksaanKesehatan::create([
            ...$request->pemeriksaanData($anggota),
            'petugas_id' => Auth::id(),
        ]);

        $message = "Raport kesehatan {$anggota->nama} berhasil disimpan.";

        if ($request->boolean('lanjut')) {
            return $this->lanjutKeBerikutnya($anggota, $semesterBerjalan, $tahunAjaranBerjalan, $message);
        }

        if ($request->input('redirect_to') === 'anggota.show') {
            return redirect()->route('anggota.show', $anggota)->with('success', $message);
        }

        return redirect()->route($request->input('redirect_to', 'pemeriksaan.index'))->with('success', $message);
    }

    public function edit(PemeriksaanKesehatan $pemeriksaan)
    {
        return view('pages.pemeriksaan.edit', [
            'layout'          => $this->layout,
            'pemeriksaan'     => $pemeriksaan,
            'selectedAnggota' => $pemeriksaan->anggota()->with('jenjang')->first(),
            'tahunOptions'    => TahunAjaran::options(),
        ]);
    }

    public function update(PemeriksaanRequest $request, PemeriksaanKesehatan $pemeriksaan)
    {
        $anggota = Anggota::findOrFail($request->anggota_id);

        if ($this->sudahAdaPemeriksaan($anggota->id, (int) $request->semester, (int) $request->tahun_ajaran, $pemeriksaan->id)) {
            return back()->withInput()->withErrors([
                'semester' => "{$anggota->nama} sudah memiliki raport kesehatan lain untuk semester dan tahun ajaran tersebut.",
            ]);
        }

        $pemeriksaan->update($request->pemeriksaanData($anggota));

        return redirect()->route('pemeriksaan.show', $pemeriksaan)
                         ->with('success', 'Data pemeriksaan berhasil diperbarui.');
    }

    public function show(PemeriksaanKesehatan $pemeriksaan)
    {
        $pemeriksaan->load(['anggota.jenjang', 'petugas']);

        return view('pages.pemeriksaan.show', [
            'layout'      => $this->layout,
            'pemeriksaan' => $pemeriksaan,
            'backUrl'     => request('back') === 'profile'
                ? route('anggota.show', $pemeriksaan->anggota_id)
                : route('pemeriksaan.index', ['semester' => $pemeriksaan->semester, 'tahun_ajaran' => $pemeriksaan->tahun_ajaran]),
        ]);
    }

    public function raport(PemeriksaanKesehatan $pemeriksaan)
    {
        $pemeriksaan->load(['anggota.jenjang', 'petugas']);

        $html = view('exports.mcu-raport', [
            'pemeriksaan' => $pemeriksaan,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->render();

        $filename = 'raport-kesehatan-' . Str::slug($pemeriksaan->anggota->nama) . '-' . $pemeriksaan->tahun_ajaran . '-s' . $pemeriksaan->semester . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function destroy(PemeriksaanKesehatan $pemeriksaan)
    {
        $pemeriksaan->delete();

        return redirect()->route('pemeriksaan.index')
                         ->with('success', 'Data pemeriksaan berhasil dihapus.');
    }

    /**
     * Mode input beruntun: buka lagi form untuk anggota berikutnya di jenjang/kelas yang sama.
     */
    private function lanjutKeBerikutnya(Anggota $anggota, int $semester, int $tahunAjaran, string $message)
    {
        $berikutnya = Anggota::aktif()
            ->where('anggota.tipe', $anggota->tipe)
            ->where('anggota.jenjang_id', $anggota->jenjang_id)
            ->when($anggota->tipe === 'siswa' && $anggota->kelas, fn ($q) => $q->where('anggota.kelas', $anggota->kelas))
            ->belumDiperiksa($semester, $tahunAjaran)
            ->urutJenjang()
            ->first();

        if (!$berikutnya) {
            $kelompok = $anggota->tipe === 'siswa' && $anggota->kelas ? "kelas {$anggota->kelas}" : strtolower($anggota->tipe_label) . ' di jenjang ini';

            return back()->with('success', "{$message} Semua {$kelompok} sudah diperiksa.");
        }

        return back()
            ->with('success', "{$message} Lanjut ke {$berikutnya->nama}.")
            ->with('lanjut_anggota_id', $berikutnya->id);
    }

    private function sudahAdaPemeriksaan(int $anggotaId, int $semester, int $tahunAjaran, ?int $kecualiId = null): bool
    {
        return PemeriksaanKesehatan::where('anggota_id', $anggotaId)
            ->periode($semester, $tahunAjaran)
            ->when($kecualiId, fn ($q) => $q->whereKeyNot($kecualiId))
            ->exists();
    }
}
