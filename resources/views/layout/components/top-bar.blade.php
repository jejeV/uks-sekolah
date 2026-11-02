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

    <!-- BEGIN: Pencarian cepat -->
    <div class="intro-x relative mr-3 sm:mr-6" data-global-search data-url="{{ route('anggota.options') }}">
        <label for="global-search" class="sr-only">Cari siswa atau guru</label>
        <div class="relative">
            <i data-feather="search" class="w-4 h-4 absolute inset-y-0 my-auto left-3 text-slate-400"></i>
            <input id="global-search" type="search" autocomplete="off"
                   class="form-control w-44 sm:w-72 pl-9 pr-8 rounded-full border-transparent bg-slate-200/70 dark:bg-darkmode-400 focus:bg-white dark:focus:bg-darkmode-600"
                   placeholder="Cari siswa / guru..."
                   role="combobox" aria-expanded="false" aria-controls="global-search-results" aria-autocomplete="list">
            <kbd class="hidden sm:flex absolute inset-y-0 my-auto right-3 h-5 items-center px-1.5 rounded border border-slate-300 dark:border-darkmode-300 text-[10px] text-slate-400" aria-hidden="true">/</kbd>
        </div>
        <div id="global-search-results" role="listbox" aria-label="Hasil pencarian"
             class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] box shadow-lg z-50 max-h-96 overflow-y-auto py-1"></div>
    </div>
    <!-- END: Pencarian cepat -->

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
