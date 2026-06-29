<?php
namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Jenjang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        if ($request->filled('kelas'))     $query->where('kelas', $request->kelas);

        $ringkasanQuery = Anggota::query();

        if ($request->filled('search')) {
            $ringkasanQuery->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nis_nip', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('jenjang_id')) $ringkasanQuery->where('jenjang_id', $request->jenjang_id);
        if ($request->filled('kelas'))     $ringkasanQuery->where('kelas', $request->kelas);

        $ringkasan = [
            'siswa' => (clone $ringkasanQuery)->where('tipe', 'siswa')->count(),
            'guru' => (clone $ringkasanQuery)->where('tipe', 'guru')->count(),
            'kelas' => (clone $ringkasanQuery)->where('tipe', 'siswa')
                ->whereNotNull('kelas')->where('kelas', '!=', '')
                ->distinct()->count('kelas'),
            'laki_laki' => (clone $ringkasanQuery)->where('jenis_kelamin', 'L')->count(),
            'perempuan' => (clone $ringkasanQuery)->where('jenis_kelamin', 'P')->count(),
        ];

        return view('pages.anggota.index', [
            'layout'  => $this->layout,
            'anggota' => $query->orderBy('nama')->paginate(15)->withQueryString(),
            'ringkasan' => $ringkasan,
            'jenjang' => Jenjang::all(),
            'kelasOptions' => Anggota::where('tipe', 'siswa')
                ->when($request->filled('jenjang_id'), fn ($query) => $query->where('jenjang_id', $request->jenjang_id))
                ->whereNotNull('kelas')
                ->where('kelas', '!=', '')
                ->distinct()
                ->orderBy('kelas')
                ->pluck('kelas'),
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'file.required' => 'Pilih file CSV yang akan diimport.',
            'file.mimes' => 'File import harus berformat CSV.',
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return redirect()->route('anggota.index')
                ->withErrors(['file' => 'File import tidak dapat dibaca.']);
        }

        $headerLine = fgets($handle);
        if ($headerLine === false) {
            fclose($handle);

            return redirect()->route('anggota.index')
                ->withErrors(['file' => 'File import kosong.']);
        }

        $delimiter = $this->detectCsvDelimiter($headerLine);
        $headers = array_map(fn ($header) => $this->normalizeHeader($header), str_getcsv($headerLine, $delimiter));

        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $errors = [];
        $rowNumber = 1;

        DB::transaction(function () use ($handle, $delimiter, $headers, &$stats, &$errors, &$rowNumber) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;

                if ($this->isEmptyCsvRow($row)) {
                    continue;
                }

                $data = $this->mapCsvRow($headers, $row);
                $result = $this->importAnggotaRow($data, $rowNumber);

                if ($result['status'] === 'created') {
                    $stats['created']++;
                } elseif ($result['status'] === 'updated') {
                    $stats['updated']++;
                } else {
                    $stats['skipped']++;
                    $errors[] = $result['message'];
                }
            }
        });

        fclose($handle);

        $message = "Import selesai. {$stats['created']} data baru, {$stats['updated']} data diperbarui, {$stats['skipped']} data dilewati.";

        return redirect()->route('anggota.index')
            ->with('success', $message)
            ->with('import_errors', array_slice($errors, 0, 10));
    }

    public function importTemplate()
    {
        $rows = [
            ['nis_nip', 'nama', 'jenjang', 'tipe', 'kelas', 'tgl_lahir', 'jenis_kelamin'],
            ['SD001', 'Nabila Azzahra', 'SD', 'siswa', '2A', '2018-04-21', 'P'],
            ['G001', 'Ibu Ratna Sari', 'SD', 'guru', '', '1987-05-08', 'P'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'template-import-anggota.csv', [
            'Content-Type' => 'text/csv',
        ]);
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

    private function importAnggotaRow(array $data, int $rowNumber): array
    {
        $nama = $this->cleanValue($data['nama'] ?? null);
        $nisNip = $this->cleanValue($data['nis_nip'] ?? null);

        if (!$nama) {
            return ['status' => 'skipped', 'message' => "Baris {$rowNumber}: nama wajib diisi."];
        }

        $anggota = $this->findExistingAnggotaForImport($nama, $nisNip);
        $payload = $this->buildAnggotaPayload($data);

        if ($anggota) {
            if (isset($payload['nis_nip'])) {
                $nisDipakai = Anggota::where('nis_nip', $payload['nis_nip'])
                    ->where('id', '!=', $anggota->id)
                    ->exists();

                if ($nisDipakai) {
                    unset($payload['nis_nip']);
                }
            }

            $anggota->update($payload);

            return ['status' => 'updated'];
        }

        $missing = [];
        foreach (['nis_nip', 'jenjang_id', 'tipe', 'jenis_kelamin'] as $field) {
            if (empty($payload[$field])) {
                $missing[] = $field;
            }
        }

        if ($missing) {
            return ['status' => 'skipped', 'message' => "Baris {$rowNumber}: data baru membutuhkan " . implode(', ', $missing) . '.'];
        }

        $payload['nama'] = $nama;
        Anggota::create($payload);

        return ['status' => 'created'];
    }

    private function findExistingAnggotaForImport(string $nama, ?string $nisNip): ?Anggota
    {
        $namaKey = $this->normalizeNameKey($nama);
        $matchesByName = Anggota::orderBy('id')
            ->get()
            ->filter(fn (Anggota $anggota) => $this->normalizeNameKey($anggota->nama) === $namaKey)
            ->values();

        if ($matchesByName->isNotEmpty()) {
            if ($nisNip) {
                $matchByNis = $matchesByName->firstWhere('nis_nip', $nisNip);
                if ($matchByNis) {
                    return $matchByNis;
                }
            }

            return $matchesByName->first();
        }

        return $nisNip ? Anggota::where('nis_nip', $nisNip)->first() : null;
    }

    private function buildAnggotaPayload(array $data): array
    {
        $payload = [];

        foreach (['nis_nip', 'nama', 'kelas'] as $field) {
            $value = $this->cleanValue($data[$field] ?? null);
            if ($value !== null) {
                $payload[$field] = $value;
            }
        }

        $jenjangId = $this->resolveJenjangId($data);
        if ($jenjangId) {
            $payload['jenjang_id'] = $jenjangId;
        }

        $tipe = $this->normalizeTipe($data['tipe'] ?? null);
        if ($tipe) {
            $payload['tipe'] = $tipe;
        }

        $jenisKelamin = $this->normalizeJenisKelamin($data['jenis_kelamin'] ?? null);
        if ($jenisKelamin) {
            $payload['jenis_kelamin'] = $jenisKelamin;
        }

        $tglLahir = $this->normalizeDate($data['tgl_lahir'] ?? null);
        if ($tglLahir) {
            $payload['tgl_lahir'] = $tglLahir;
        }

        $aktif = $this->normalizeBoolean($data['aktif'] ?? null);
        if ($aktif !== null) {
            $payload['aktif'] = $aktif;
        }

        return $payload;
    }

    private function resolveJenjangId(array $data): ?int
    {
        $jenjangId = $this->cleanValue($data['jenjang_id'] ?? null);
        if ($jenjangId && ctype_digit($jenjangId) && Jenjang::whereKey($jenjangId)->exists()) {
            return (int) $jenjangId;
        }

        $jenjangNama = $this->cleanValue($data['jenjang'] ?? null);
        if (!$jenjangNama) {
            return null;
        }

        $jenjang = Jenjang::where(DB::raw('LOWER(nama)'), Str::lower($jenjangNama))->first();

        return $jenjang?->id;
    }

    private function detectCsvDelimiter(string $line): string
    {
        $delimiters = [',' => 0, ';' => 0, "\t" => 0];

        foreach ($delimiters as $delimiter => $count) {
            $delimiters[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($delimiters);

        return array_key_first($delimiters);
    }

    private function normalizeHeader(string $header): string
    {
        $key = Str::lower(trim($header));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');

        $aliases = [
            'nis' => 'nis_nip',
            'nip' => 'nis_nip',
            'nis_nip' => 'nis_nip',
            'nisnip' => 'nis_nip',
            'nomor_induk' => 'nis_nip',
            'nama_lengkap' => 'nama',
            'name' => 'nama',
            'tingkat' => 'jenjang',
            'jenjang_id' => 'jenjang_id',
            'type' => 'tipe',
            'status' => 'tipe',
            'tanggal_lahir' => 'tgl_lahir',
            'tgl_lahir' => 'tgl_lahir',
            'lahir' => 'tgl_lahir',
            'jk' => 'jenis_kelamin',
            'gender' => 'jenis_kelamin',
            'jenis_kelamin' => 'jenis_kelamin',
            'is_active' => 'aktif',
        ];

        return $aliases[$key] ?? $key;
    }

    private function mapCsvRow(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            if (!$header) {
                continue;
            }

            $data[$header] = $row[$index] ?? null;
        }

        return $data;
    }

    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cleanValue($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function cleanValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeNameKey($value): ?string
    {
        $value = $this->cleanValue($value);

        if (!$value) {
            return null;
        }

        return Str::lower(preg_replace('/\s+/', ' ', $value));
    }

    private function normalizeTipe($value): ?string
    {
        $value = Str::lower((string) $this->cleanValue($value));
        $value = str_replace([' ', '-'], '_', $value);

        $aliases = [
            'siswa' => 'siswa',
            'murid' => 'siswa',
            'guru' => 'guru',
            'staff' => 'tenaga_kependidikan',
            'staf' => 'tenaga_kependidikan',
            'pegawai' => 'tenaga_kependidikan',
            'tenaga_kependidikan' => 'tenaga_kependidikan',
        ];

        return $aliases[$value] ?? null;
    }

    private function normalizeJenisKelamin($value): ?string
    {
        $value = Str::lower((string) $this->cleanValue($value));

        if (in_array($value, ['l', 'laki', 'laki_laki', 'laki-laki', 'male', 'pria'], true)) {
            return 'L';
        }

        if (in_array($value, ['p', 'perempuan', 'female', 'wanita'], true)) {
            return 'P';
        }

        return null;
    }

    private function normalizeDate($value): ?string
    {
        $value = $this->cleanValue($value);

        if (!$value) {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Exception $exception) {
                //
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function normalizeBoolean($value): ?bool
    {
        $value = Str::lower((string) $this->cleanValue($value));

        if (in_array($value, ['1', 'true', 'ya', 'yes', 'aktif'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'tidak', 'no', 'nonaktif'], true)) {
            return false;
        }

        return null;
    }
}
