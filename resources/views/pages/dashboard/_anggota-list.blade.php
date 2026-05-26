<div class="overflow-auto">
    <table class="table table-report">
        <thead>
            <tr>
                <th class="whitespace-nowrap">Nama</th>
                <th class="whitespace-nowrap">Tipe</th>
                <th class="whitespace-nowrap">Jenjang</th>
                <th class="whitespace-nowrap">Kelas</th>
                <th class="whitespace-nowrap">NIS / NIP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $anggota)
                <tr>
                    <td>
                        <a href="{{ route('anggota.show', $anggota) }}" class="font-medium text-primary whitespace-nowrap">
                            {{ $anggota->nama }}
                        </a>
                    </td>
                    <td class="capitalize whitespace-nowrap">{{ str_replace('_', ' ', $anggota->tipe) }}</td>
                    <td class="whitespace-nowrap">{{ optional($anggota->jenjang)->nama ?? '-' }}</td>
                    <td class="whitespace-nowrap">{{ $anggota->kelas ?: '-' }}</td>
                    <td class="whitespace-nowrap">{{ $anggota->nis_nip }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-slate-500 py-6">{{ $empty }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
