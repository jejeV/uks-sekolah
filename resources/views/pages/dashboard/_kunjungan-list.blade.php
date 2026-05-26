<div class="overflow-auto">
    <table class="table table-report">
        <thead>
            <tr>
                <th class="whitespace-nowrap">Nama</th>
                <th class="whitespace-nowrap">Tanggal</th>
                <th class="whitespace-nowrap">Keluhan</th>
                <th class="whitespace-nowrap">Status</th>
                <th class="whitespace-nowrap">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $kunjungan)
                <tr>
                    <td>
                        @if ($kunjungan->anggota)
                            <a href="{{ route('anggota.show', $kunjungan->anggota) }}" class="font-medium text-primary whitespace-nowrap">
                                {{ $kunjungan->anggota->nama }}
                            </a>
                        @else
                            <span class="font-medium">-</span>
                        @endif
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ optional(optional($kunjungan->anggota)->jenjang)->nama ?? '-' }}
                        </div>
                    </td>
                    <td class="whitespace-nowrap">
                        {{ $kunjungan->tanggal->format('d/m/Y') }}
                        <div class="text-xs text-slate-500 mt-0.5">{{ $kunjungan->jam ?: '-' }}</div>
                    </td>
                    <td>
                        <div class="font-medium truncate max-w-sm">{{ $kunjungan->keluhan }}</div>
                        <div class="text-xs text-slate-500 truncate max-w-sm">{{ $kunjungan->diagnosis ?: 'Belum ada diagnosis' }}</div>
                    </td>
                    <td>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 capitalize">
                            {{ $kunjungan->status }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap">{{ optional($kunjungan->petugas)->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-6">{{ $empty }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
