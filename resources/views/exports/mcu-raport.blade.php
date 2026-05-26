<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Raport MCU {{ $pemeriksaan->anggota->nama }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            border-bottom: 3px solid #1e40af;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 22px;
            margin: 0;
        }

        .muted {
            color: #64748b;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .grid td,
        .grid th {
            border: 1px solid #d7dce5;
            padding: 9px 10px;
            vertical-align: top;
        }

        .grid th {
            width: 30%;
            text-align: left;
            background: #eef2f7;
        }

        .section {
            margin-top: 20px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .sign {
            width: 100%;
            margin-top: 34px;
        }

        .sign td {
            width: 50%;
            text-align: center;
            padding-top: 24px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Raport Hasil MCU UKS</h1>
        <div class="muted">Dokumen informasi kesehatan siswa untuk orang tua/wali. Dibuat pada {{ $generatedAt }}.</div>
    </div>

    <div class="section">
        <div class="section-title">Identitas Siswa</div>
        <table class="grid">
            <tr>
                <th>Nama</th>
                <td>{{ $pemeriksaan->anggota->nama }}</td>
            </tr>
            <tr>
                <th>NIS</th>
                <td>{{ $pemeriksaan->anggota->nis_nip }}</td>
            </tr>
            <tr>
                <th>Jenjang / Kelas</th>
                <td>{{ $pemeriksaan->anggota->jenjang?->nama ?? '-' }} / {{ $pemeriksaan->anggota->kelas ?: '-' }}</td>
            </tr>
            <tr>
                <th>Semester / Tahun Ajaran</th>
                <td>Semester {{ $pemeriksaan->semester }} / {{ $pemeriksaan->tahun_ajaran }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Hasil Pemeriksaan</div>
        <table class="grid">
            <tr>
                <th>Berat Badan</th>
                <td>{{ $pemeriksaan->berat_badan ?? '-' }} kg</td>
            </tr>
            <tr>
                <th>Tinggi Badan</th>
                <td>{{ $pemeriksaan->tinggi_badan ?? '-' }} cm</td>
            </tr>
            <tr>
                <th>BMI</th>
                <td>{{ $pemeriksaan->bmi ?? '-' }}</td>
            </tr>
            <tr>
                <th>Penglihatan</th>
                <td>Kiri: {{ $pemeriksaan->penglihatan_kiri ?: '-' }} | Kanan: {{ $pemeriksaan->penglihatan_kanan ?: '-' }}</td>
            </tr>
            <tr>
                <th>Pendengaran</th>
                <td>{{ $pemeriksaan->pendengaran ? ucfirst($pemeriksaan->pendengaran) : '-' }}</td>
            </tr>
            <tr>
                <th>Kondisi Gigi</th>
                <td>{{ $pemeriksaan->kondisi_gigi ? ucfirst(str_replace('_', ' ', $pemeriksaan->kondisi_gigi)) : '-' }}</td>
            </tr>
            <tr>
                <th>Catatan Petugas UKS</th>
                <td>{{ $pemeriksaan->catatan ?: 'Tidak ada catatan khusus.' }}</td>
            </tr>
        </table>
    </div>

    <table class="sign">
        <tr>
            <td>
                Orang Tua/Wali
                <br><br><br>
                (................................)
            </td>
            <td>
                Petugas UKS
                <br><br><br>
                ({{ $pemeriksaan->petugas?->name ?? '................................' }})
            </td>
        </tr>
    </table>
</body>
</html>
