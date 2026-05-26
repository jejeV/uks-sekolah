<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .meta {
            color: #64748b;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d7dce5;
            padding: 7px 8px;
            vertical-align: top;
        }

        th {
            background: #eef2f7;
            color: #111827;
            font-weight: 700;
            text-align: left;
        }

        tr:nth-child(even) td {
            background: #f8fafc;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 24px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Dibuat pada {{ $generatedAt }}</div>

    <table>
        <thead>
            <tr>
                @foreach (($rows->first() ? array_keys($rows->first()) : ['Informasi']) as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="1">Tidak ada data untuk diexport.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
