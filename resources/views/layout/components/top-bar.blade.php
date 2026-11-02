@php
    $moduleLabels = [
        'dashboard'   => ['Dashboard', 'dashboard'],
        'kunjungan'   => ['Kunjungan UKS', 'kunjungan.index'],
        'pemeriksaan' => ['Raport Kesehatan', 'pemeriksaan.index'],
        'anggota'     => ['Siswa & Guru', 'anggota.index'],
    ];
    $actionLabels = ['create' => 'Tambah', 'edit' => 'Ubah', 'show' => 'Detail'];
    [$module, $action] = array_pad(explode('.', $page_name ?? 'dashboard', 2), 2, null);
    $moduleInfo = $moduleLabels[$module] ?? [ucfirst($module), null];
    $userName = Auth::user()->name ?? 'Pengguna';
    $initials = collect(preg_split('/\s+/', trim($userName)))->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->implode('');
    $colorSchemes = ['default' => 'bg-blue-800', 'theme-1' => 'bg-emerald-900', 'theme-2' => 'bg-blue-900', 'theme-3' => 'bg-cyan-900', 'theme-4' => 'bg-indigo-900'];
@endphp

<!-- BEGIN: Top Bar -->
<div class="top-bar">
    <!-- BEGIN: Breadcrumb -->
    <nav aria-label="breadcrumb" class="-intro-x mr-auto hidden sm:flex">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">UKS</a></li>
            @if (isset($actionLabels[$action]) && $moduleInfo[1])
                <li class="breadcrumb-item"><a href="{{ route($moduleInfo[1]) }}">{{ $moduleInfo[0] }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $actionLabels[$action] }}</li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $moduleInfo[0] }}</li>
            @endif
        </ol>
    </nav>
    <!-- END: Breadcrumb -->

    <a href="{{ route('dark-mode-switcher') }}" class="intro-x mr-4 sm:mr-6 text-slate-500 hover:text-primary tooltip"
       title="{{ $dark_mode ? 'Mode terang' : 'Mode gelap' }}" aria-label="Ganti mode tampilan">
        <i data-feather="{{ $dark_mode ? 'sun' : 'moon' }}" class="w-5 h-5"></i>
    </a>

    <!-- BEGIN: Account Menu -->
    <div class="intro-x dropdown w-8 h-8">
        <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg zoom-in" role="button" aria-expanded="false" aria-label="Menu akun" data-tw-toggle="dropdown">
            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs uppercase">
                {{ $initials ?: 'U' }}
            </div>
        </div>
        <div class="dropdown-menu w-56">
            <ul class="dropdown-content bg-primary text-white">
                <li class="p-2">
                    <div class="font-medium">{{ $userName }}</div>
                    <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">{{ ucfirst(Auth::user()->role ?? '') }}</div>
                </li>
                <li><hr class="dropdown-divider border-white/[0.08]"></li>
                <li class="p-2">
                    <div class="text-xs text-white/70 mb-2">Warna tema</div>
                    <div class="flex gap-2">
                        @foreach ($colorSchemes as $scheme => $bg)
                            <a href="{{ route('color-scheme-switcher', ['color_scheme' => $scheme]) }}"
                               class="block w-6 h-6 rounded-full border-2 {{ $bg }} {{ $color_scheme === $scheme ? 'border-white' : 'border-white/20 hover:border-white/60' }}"
                               aria-label="Tema {{ $loop->iteration }}"></a>
                        @endforeach
                    </div>
                </li>
                <li><hr class="dropdown-divider border-white/[0.08]"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item hover:bg-white/5 w-full text-left">
                            <i data-feather="log-out" class="w-4 h-4 mr-2"></i> Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <!-- END: Account Menu -->
</div>
<!-- END: Top Bar -->
