<!-- BEGIN: Top Bar -->
<div class="top-bar">
    <!-- BEGIN: Breadcrumb -->
    <nav aria-label="breadcrumb" class="-intro-x mr-auto hidden sm:flex">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Aplikasi</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($page_name ?? 'Dashboard') }}</li>
        </ol>
    </nav>
    <!-- END: Breadcrumb -->
    <!-- BEGIN: Account Menu -->
    <div class="intro-x dropdown w-8 h-8">
        <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in" role="button" aria-expanded="false" data-tw-toggle="dropdown">
            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-xs uppercase shadow">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
            </div>
        </div>
        <div class="dropdown-menu w-56">
            <ul class="dropdown-content bg-primary text-white">
                <li class="p-2">
                    <div class="font-medium">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-white/70 mt-0.5 dark:text-slate-500">{{ ucfirst(Auth::user()->role) }}</div>
                </li>
                <li><hr class="dropdown-divider border-white/[0.08]"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item hover:bg-white/5 w-full text-left">
                            <i data-feather="toggle-right" class="w-4 h-4 mr-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <!-- END: Account Menu -->
</div>
<!-- END: Top Bar -->
