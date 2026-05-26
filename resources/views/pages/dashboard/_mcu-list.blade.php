<div class="overflow-auto">
    <table class="table table-report">
        <thead>
            <tr>
                <th class="whitespace-nowrap">Nama</th>
                <th class="whitespace-nowrap">Periode</th>
                <th class="whitespace-nowrap">BB / TB</th>
                <th class="whitespace-nowrap">Penglihatan</th>
                <th class="whitespace-nowrap">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $mcu)
                <tr>
                    <td>
                        @if ($mcu->anggota)
                            <a href="{{ route('anggota.show', $mcu->anggota) }}" class="font-medium text-primary whitespace-nowrap">
                                {{ $mcu->anggota->nama }}
                            </a>
                        @else
                            <span class="font-medium">-</span>
                        @endif
                        <div class="text-xs text-slate-500 mt-0.5">{{ optional(optional($mcu->anggota)->jenjang)->nama ?? '-' }}</div>
                    </td>
                    <td class="whitespace-nowrap">Semester {{ $mcu->semester }} / {{ $mcu->tahun_ajaran }}</td>
                    <td class="whitespace-nowrap">{{ $mcu->berat_badan ?: '-' }} kg / {{ $mcu->tinggi_badan ?: '-' }} cm</td>
                    <td class="whitespace-nowrap">{{ $mcu->penglihatan_kiri ?: '-' }} / {{ $mcu->penglihatan_kanan ?: '-' }}</td>
                    <td class="whitespace-nowrap">{{ optional($mcu->petugas)->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-6">{{ $empty }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
