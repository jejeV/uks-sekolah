<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Jenjang;
use App\Models\PemeriksaanKesehatan;
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
        $query = PemeriksaanKesehatan::with(['anggota.jenjang', 'petugas']);
        $widgetStatus = $request->input('status_pemeriksaan');
        $widgetTipe = $request->input('tipe_anggota');
        $isBelumMode = $widgetStatus === 'belum' && in_array($widgetTipe, ['siswa', 'guru'], true);
        $isSudahMode = $widgetStatus === 'sudah' && in_array($widgetTipe, ['siswa', 'guru'], true);

        if ($request->filled('semester'))    $query->where('semester', $request->semester);
        if ($request->filled('tahun_ajaran')) $query->where('tahun_ajaran', $request->tahun_ajaran);
        if ($request->filled('jenjang_id')) {
            $query->whereHas('anggota', fn ($anggota) => $anggota->where('jenjang_id', $request->jenjang_id));
        }
        if ($request->filled('kelas')) {
            $query->whereHas('anggota', fn ($anggota) => $anggota->where('kelas', $request->kelas));
        }
        if ($request->filled('search')) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }
        if ($isSudahMode) {
            $query->whereHas('anggota', fn ($anggota) => $anggota->where('tipe', $widgetTipe));
        }

        $semesterRingkasan = $request->filled('semester')
            ? (int) $request->semester
            : (now()->month >= 7 ? 1 : 2);
        $tahunAjaranRingkasan = $request->filled('tahun_ajaran')
            ? (int) $request->tahun_ajaran
            : (now()->month >= 7 ? now()->year : now()->year - 1);

        $belumBase = fn (?string $tipe = null) => $this->anggotaBelumDiperiksaQuery($request, $semesterRingkasan, $tahunAjaranRingkasan, $tipe);

        $ringkasan = [
            'total' => (clone $query)->count(),
            'siswa_diperiksa' => $this->pemeriksaanCountByTipe($request, 'siswa'),
            'guru_diperiksa' => $this->pemeriksaanCountByTipe($request, 'guru'),
            'siswa_belum_diperiksa' => $belumBase('siswa')->count(),
            'guru_belum_diperiksa' => $belumBase('guru')->count(),
            'perlu_tindak_lanjut' => (clone $query)->where(function ($query) {
                $query->whereIn('pendengaran', ['kurang', 'tuli'])
                    ->orWhereIn('kondisi_gigi', ['caries', 'perlu_perawatan']);
            })->count(),
        ];

        $query->join('anggota', 'pemeriksaan_kesehatan.anggota_id', '=', 'anggota.id')
            ->leftJoin('jenjang', 'anggota.jenjang_id', '=', 'jenjang.id')
            ->select('pemeriksaan_kesehatan.*')
            ->orderByRaw("
                CASE
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'TK' THEN 1
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SD' THEN 2
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SMP' THEN 3
                    WHEN anggota.tipe = 'guru' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('anggota.kelas')
            ->orderBy('anggota.nama')
            ->latest('pemeriksaan_kesehatan.created_at');

        $belumDiperiksa = $isBelumMode
            ? $belumBase($widgetTipe)
                ->with('jenjang')
                ->leftJoin('jenjang', 'anggota.jenjang_id', '=', 'jenjang.id')
                ->select('anggota.*')
                ->orderByRaw("
                    CASE
                        WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'TK' THEN 1
                        WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SD' THEN 2
                        WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SMP' THEN 3
                        WHEN anggota.tipe = 'guru' THEN 4
                        ELSE 5
                    END
                ")
                ->orderBy('anggota.kelas')
                ->orderBy('anggota.nama')
                ->paginate(15)
                ->withQueryString()
            : null;

        $daftarRaport = Anggota::with([
                'jenjang',
                'pemeriksaan' => function ($query) use ($semesterRingkasan, $tahunAjaranRingkasan) {
                    $query->where('semester', $semesterRingkasan)
                        ->where('tahun_ajaran', $tahunAjaranRingkasan);
                },
            ])
            ->where('aktif', true)
            ->whereIn('tipe', ['siswa', 'guru'])
            ->when(in_array($widgetTipe, ['siswa', 'guru'], true), fn ($query) => $query->where('tipe', $widgetTipe))
            ->when($request->filled('jenjang_id'), fn ($query) => $query->where('anggota.jenjang_id', $request->jenjang_id))
            ->when($request->filled('kelas'), fn ($query) => $query->where('anggota.kelas', $request->kelas))
            ->when($request->filled('search'), fn ($query) => $query->where('anggota.nama', 'like', '%' . $request->search . '%'))
            ->when($isSudahMode, function ($query) use ($semesterRingkasan, $tahunAjaranRingkasan) {
                $query->whereHas('pemeriksaan', function ($pemeriksaan) use ($semesterRingkasan, $tahunAjaranRingkasan) {
                    $pemeriksaan->where('semester', $semesterRingkasan)
                        ->where('tahun_ajaran', $tahunAjaranRingkasan);
                });
            })
            ->when($isBelumMode, function ($query) use ($semesterRingkasan, $tahunAjaranRingkasan) {
                $query->whereDoesntHave('pemeriksaan', function ($pemeriksaan) use ($semesterRingkasan, $tahunAjaranRingkasan) {
                    $pemeriksaan->where('semester', $semesterRingkasan)
                        ->where('tahun_ajaran', $tahunAjaranRingkasan);
                });
            })
            ->leftJoin('jenjang', 'anggota.jenjang_id', '=', 'jenjang.id')
            ->select('anggota.*')
            ->orderByRaw("
                CASE
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'TK' THEN 1
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SD' THEN 2
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SMP' THEN 3
                    WHEN anggota.tipe = 'guru' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('anggota.kelas')
            ->orderBy('anggota.nama')
            ->paginate(15)
            ->withQueryString();

        $activeWidgetLabel = null;
        if ($isSudahMode || $isBelumMode) {
            $activeWidgetLabel = ucfirst($widgetTipe) . ' ' . ($widgetStatus === 'sudah' ? 'sudah diperiksa' : 'belum diperiksa');
        }

        return view('pages.pemeriksaan.index', [
            'layout'       => $this->layout,
            'pemeriksaan'  => $query->paginate(15)->withQueryString(),
            'belumDiperiksa' => $belumDiperiksa,
            'daftarRaport' => $daftarRaport,
            'activeWidgetLabel' => $activeWidgetLabel,
            'widgetStatus' => $widgetStatus,
            'widgetTipe' => $widgetTipe,
            'semesterRingkasan' => $semesterRingkasan,
            'tahunAjaranRingkasan' => $tahunAjaranRingkasan,
            'ringkasan'    => $ringkasan,
            'tahunOptions' => range(now()->year, now()->year - 5),
            'anggota'      => $this->anggotaPemeriksaanOptions(),
            'jenjang'      => Jenjang::orderBy('nama')->get(),
            'kelasOptions' => Anggota::where('tipe', 'siswa')
                ->when($request->filled('jenjang_id'), fn ($query) => $query->where('jenjang_id', $request->jenjang_id))
                ->whereNotNull('kelas')->where('kelas', '!=', '')
                ->distinct()->orderBy('kelas')->pluck('kelas'),
        ]);
    }

    public function create()
    {
        return view('pages.pemeriksaan.create', [
            'layout'       => $this->layout,
            'anggota'      => $this->anggotaPemeriksaanOptions(),
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
            'gula_darah'        => 'nullable|numeric|min:0|max:1000',
            'kolesterol'        => 'nullable|numeric|min:0|max:1000',
            'catatan'           => 'nullable|string',
            'redirect_to'        => 'nullable|in:dashboard,pemeriksaan.index,anggota.show',
        ]);

        $semesterBerjalan = now()->month >= 7 ? 1 : 2;
        $tahunAjaranBerjalan = now()->month >= 7 ? now()->year : now()->year - 1;

        if ((int) $request->semester !== $semesterBerjalan || (int) $request->tahun_ajaran !== $tahunAjaranBerjalan) {
            return back()->withInput()->withErrors([
                'semester' => 'Input raport kesehatan hanya dapat dilakukan pada Semester ' . $semesterBerjalan . ' tahun ajaran ' . $tahunAjaranBerjalan . ' (Semester 1: Juli-Desember; Semester 2: Januari-Juni).',
            ]);
        }

        $anggota = Anggota::findOrFail($request->anggota_id);
        $tipeAnggota = str_replace('_', ' ', $anggota->tipe);

        $sudahAda = PemeriksaanKesehatan::where('anggota_id', $request->anggota_id)
            ->where('semester', $request->semester)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->exists();

        if ($sudahAda) {
            return back()
                ->withInput()
                ->withErrors(['semester' => 'Raport kesehatan ' . $tipeAnggota . ' untuk semester dan tahun ajaran ini sudah tercatat.']);
        }

        $bmi = null;
        if ($request->filled('berat_badan') && $request->filled('tinggi_badan')) {
            $tinggiMeter = $request->tinggi_badan / 100;
            $bmi = round($request->berat_badan / ($tinggiMeter * $tinggiMeter), 2);
        }

        $data = $request->except('_token', 'redirect_to');
        if ($anggota->tipe !== 'guru') {
            $data['gula_darah'] = null;
            $data['kolesterol'] = null;
        }

        PemeriksaanKesehatan::create([
            ...$data,
            'petugas_id' => Auth::id(),
            'bmi'        => $bmi,
        ]);

        if ($request->input('redirect_to') === 'anggota.show') {
            return redirect()->route('anggota.show', $request->anggota_id)
                             ->with('success', 'Data raport kesehatan ' . $tipeAnggota . ' berhasil disimpan.');
        }

        return redirect()->route($request->input('redirect_to', 'pemeriksaan.index'))
                         ->with('success', 'Data pemeriksaan berhasil disimpan.');
    }

    public function edit(PemeriksaanKesehatan $pemeriksaan)
    {
        return view('pages.pemeriksaan.edit', [
            'layout'       => $this->layout,
            'pemeriksaan'  => $pemeriksaan,
            'anggota'      => $this->anggotaPemeriksaanOptions(),
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
            'gula_darah'        => 'nullable|numeric|min:0|max:1000',
            'kolesterol'        => 'nullable|numeric|min:0|max:1000',
            'catatan'           => 'nullable|string',
        ]);

        $bmi = null;
        if ($request->filled('berat_badan') && $request->filled('tinggi_badan')) {
            $tinggiMeter = $request->tinggi_badan / 100;
            $bmi = round($request->berat_badan / ($tinggiMeter * $tinggiMeter), 2);
        }

        $anggota = Anggota::findOrFail($request->anggota_id);
        $data = $request->except('_token', '_method', 'redirect_to');
        if ($anggota->tipe !== 'guru') {
            $data['gula_darah'] = null;
            $data['kolesterol'] = null;
        }

        $pemeriksaan->update([
            ...$data,
            'bmi' => $bmi,
        ]);

        return redirect()->route('pemeriksaan.index')
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
                : route('pemeriksaan.index'),
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

    private function anggotaPemeriksaanOptions()
    {
        return Anggota::with('jenjang')
            ->where('aktif', true)
            ->whereIn('tipe', ['siswa', 'guru'])
            ->leftJoin('jenjang', 'anggota.jenjang_id', '=', 'jenjang.id')
            ->select('anggota.*')
            ->orderByRaw("
                CASE
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'TK' THEN 1
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SD' THEN 2
                    WHEN anggota.tipe = 'siswa' AND jenjang.nama = 'SMP' THEN 3
                    WHEN anggota.tipe = 'guru' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('anggota.kelas')
            ->orderBy('anggota.nama')
            ->get();
    }

    private function pemeriksaanCountByTipe(Request $request, string $tipe): int
    {
        return PemeriksaanKesehatan::whereHas('anggota', function ($anggota) use ($request, $tipe) {
                $anggota->where('tipe', $tipe)
                    ->when($request->filled('jenjang_id'), fn ($query) => $query->where('jenjang_id', $request->jenjang_id))
                    ->when($request->filled('kelas'), fn ($query) => $query->where('kelas', $request->kelas))
                    ->when($request->filled('search'), fn ($query) => $query->where('nama', 'like', '%' . $request->search . '%'));
            })
            ->when($request->filled('semester'), fn ($query) => $query->where('semester', $request->semester))
            ->when($request->filled('tahun_ajaran'), fn ($query) => $query->where('tahun_ajaran', $request->tahun_ajaran))
            ->distinct('anggota_id')
            ->count('anggota_id');
    }

    private function anggotaBelumDiperiksaQuery(Request $request, int $semester, int $tahunAjaran, ?string $tipe = null)
    {
        return Anggota::where('aktif', true)
            ->whereIn('tipe', ['siswa', 'guru'])
            ->when($tipe, fn ($query) => $query->where('tipe', $tipe))
            ->when($request->filled('jenjang_id'), fn ($query) => $query->where('anggota.jenjang_id', $request->jenjang_id))
            ->when($request->filled('kelas'), fn ($query) => $query->where('anggota.kelas', $request->kelas))
            ->when($request->filled('search'), fn ($query) => $query->where('anggota.nama', 'like', '%' . $request->search . '%'))
            ->whereDoesntHave('pemeriksaan', function ($query) use ($semester, $tahunAjaran) {
                $query->where('semester', $semester)
                    ->where('tahun_ajaran', $tahunAjaran);
            });
    }
}
