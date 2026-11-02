<?php
namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\Jenjang;
use App\Support\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnggotaController extends Controller
{
    protected $layout = 'side-menu';

    /** Indeks pencarian anggota selama satu proses import (nama => [Anggota], nis => Anggota). */
    private array $importByName = [];
    private array $importByNis = [];
    private ?array $jenjangByName = null;

    public function index(Request $request)
    {
        $filters = $request->only(['jenjang_id', 'kelas', 'search']);

        // Semua angka ringkasan diambil dalam satu query agregat.
        $ringkasan = Anggota::filter($filters)
            ->selectRaw("SUM(CASE WHEN tipe = 'siswa' THEN 1 ELSE 0 END) as siswa")
            ->selectRaw("SUM(CASE WHEN tipe = 'guru' THEN 1 ELSE 0 END) as guru")
            ->selectRaw("COUNT(DISTINCT CASE WHEN tipe = 'siswa' AND kelas <> '' THEN kelas END) as kelas")
            ->selectRaw("SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki_laki")
            ->selectRaw("SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan")
            ->toBase()
            ->first();

        return view('pages.anggota.index', [
            'layout'       => $this->layout,
            'anggota'      => Anggota::with('jenjang')
                ->filter($filters)
                ->when($request->filled('tipe'), fn ($q) => $q->where('tipe', $request->tipe))
                ->orderBy('nama')
                ->paginate(15)
                ->withQueryString(),
            'ringkasan'    => array_map('intval', (array) $ringkasan),
            'jenjang'      => Jenjang::orderBy('nama')->get(),
            'kelasOptions' => Anggota::kelasOptions($request->input('jenjang_id')),
        ]);
    }

    /**
     * Sumber data untuk kolom pencarian anggota (autocomplete) di form kunjungan & pemeriksaan.
     */
    public function options(Request $request)
    {
        return Anggota::select('id', 'jenjang_id', 'nis_nip', 'nama', 'tipe', 'kelas')
            ->with('jenjang:id,nama')
            ->aktif()
            ->when(in_array($request->input('tipe'), ['siswa', 'guru'], true), fn ($q) => $q->where('tipe', $request->tipe))
            ->when($request->input('tipe') === 'pemeriksaan', fn ($q) => $q->whereIn('tipe', ['siswa', 'guru']))
            ->filter(['search' => $request->input('q')])
            ->orderBy('nama')
            ->limit(20)
            ->get()
            ->map(fn (Anggota $anggota) => [
                'id'    => $anggota->id,
                'tipe'  => $anggota->tipe,
                'label' => $anggota->picker_label,
                'url'   => route('anggota.show', $anggota),
            ]);
    }

    public function create()
    {
        return view('pages.anggota.create', [
            'layout'  => $this->layout,
            'jenjang' => Jenjang::orderBy('nama')->get(),
        ]);
    }

    public function show(Anggota $anggota)
    {
        $anggota->load([
            'jenjang',
            'riwayatPenyakit' => fn ($query) => $query->latest('tgl_mulai'),
            'pemeriksaan' => fn ($query) => $query->with('petugas')->orderByDesc('tahun_ajaran')->orderByDesc('semester'),
            'kunjungan' => fn ($query) => $query->with('petugas')->latest('tanggal')->latest('jam')->limit(8),
        ])->loadCount('kunjungan');

        $semesterBerjalan = TahunAjaran::semester();
        $tahunAjaran = TahunAjaran::tahun();
        $mcuSemesterIni = $anggota->pemeriksaan->first(
            fn ($item) => (int) $item->semester === $semesterBerjalan && (int) $item->tahun_ajaran === $tahunAjaran
        );

        return view('pages.anggota.show', [
            'layout'           => $this->layout,
            'anggota'          => $anggota,
            'semesterBerjalan' => $semesterBerjalan,
            'tahunAjaran'      => $tahunAjaran,
            'mcuSemesterIni'   => $mcuSemesterIni,
            'riwayatAktif'     => $anggota->riwayatPenyakit->whereIn('status', ['aktif', 'kronis'])->count(),
        ]);
    }

    public function store(AnggotaRequest $request)
    {
        $anggota = Anggota::create($request->anggotaData());

        return redirect()->route($request->input('redirect_to', 'anggota.index'))
                         ->with('success', "Data {$anggota->nama} berhasil ditambahkan.");
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
        $this->buildImportIndex();

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
            'jenjang' => Jenjang::orderBy('nama')->get(),
        ]);
    }

    public function update(AnggotaRequest $request, Anggota $anggota)
    {
        $anggota->update($request->anggotaData());

        return redirect()->route('anggota.show', $anggota)
                         ->with('success', "Data {$anggota->nama} berhasil diperbarui.");
    }

    public function destroy(Anggota $anggota)
    {
        $anggota->delete();

        return redirect()->route('anggota.index')
                         ->with('success', "Data {$anggota->nama} berhasil dihapus.");
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
                $pemilikNis = $this->importByNis[$payload['nis_nip']] ?? null;

                if ($pemilikNis && $pemilikNis->id !== $anggota->id) {
                    unset($payload['nis_nip']);
                }
            }

            $anggota->update($payload);
            $this->importByNis[$anggota->nis_nip] = $anggota;

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
        $this->addToImportIndex(Anggota::create($payload));

        return ['status' => 'created'];
    }

    /**
     * Muat semua anggota sekali saja, bukan sekali per baris CSV.
     */
    private function buildImportIndex(): void
    {
        $this->importByName = [];
        $this->importByNis = [];

        Anggota::orderBy('id')->get()->each(fn (Anggota $anggota) => $this->addToImportIndex($anggota));
    }

    private function addToImportIndex(Anggota $anggota): void
    {
        $this->importByName[$this->normalizeNameKey($anggota->nama)][] = $anggota;
        $this->importByNis[$anggota->nis_nip] = $anggota;
    }

    private function findExistingAnggotaForImport(string $nama, ?string $nisNip): ?Anggota
    {
        $matchesByName = $this->importByName[$this->normalizeNameKey($nama)] ?? [];

        if ($matchesByName) {
            if ($nisNip) {
                foreach ($matchesByName as $anggota) {
                    if ($anggota->nis_nip === $nisNip) {
                        return $anggota;
                    }
                }
            }

            return $matchesByName[0];
        }

        return $nisNip ? ($this->importByNis[$nisNip] ?? null) : null;
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
        $this->jenjangByName ??= Jenjang::pluck('id', 'nama')
            ->mapWithKeys(fn ($id, $nama) => [Str::lower($nama) => (int) $id])
            ->all();

        $jenjangId = $this->cleanValue($data['jenjang_id'] ?? null);
        if ($jenjangId && ctype_digit($jenjangId) && in_array((int) $jenjangId, $this->jenjangByName, true)) {
            return (int) $jenjangId;
        }

        $jenjangNama = $this->cleanValue($data['jenjang'] ?? null);

        return $jenjangNama ? ($this->jenjangByName[Str::lower($jenjangNama)] ?? null) : null;
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
