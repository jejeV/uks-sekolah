@extends('../layout/' . $layout)

@section('head')
    <title>Login - UKS Sekolah</title>
@endsection

@section('content')
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            {{-- BEGIN: Login Info --}}
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    <img alt="UKS Sekolah" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                    <span class="text-white text-lg ml-3">UKS Sekolah</span>
                </a>
                <div class="my-auto">
                    <img alt="UKS Sekolah" class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
                        Sistem Informasi <br> Unit Kesehatan Sekolah
                    </div>
                    <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">
                        Kelola data kesehatan siswa TK, SD, dan SMP dalam satu sistem.
                    </div>
                </div>
            </div>
            {{-- END: Login Info --}}

            {{-- BEGIN: Login Form --}}
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Masuk</h2>
                    <div class="intro-x mt-2 text-slate-500 text-center xl:text-left">Gunakan akun petugas UKS Anda.</div>

                    @if ($errors->any())
                        <div class="intro-x alert alert-danger-soft show mt-6 flex items-center mb-2" role="alert">
                            <i data-feather="alert-circle" class="w-5 h-5 mr-2 flex-none"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="intro-x mt-8">
                        @csrf
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@sekolah.sch.id"
                               autocomplete="username" @unless (old('email')) autofocus @endunless
                               class="login__input form-control py-3 px-4 block {{ $errors->has('email') ? 'border-danger' : '' }}" required>

                        <label for="password" class="form-label mt-4">Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" placeholder="Password" autocomplete="current-password"
                                   @if (old('email')) autofocus @endif
                                   class="login__input form-control py-3 pl-4 pr-12 block {{ $errors->has('password') ? 'border-danger' : '' }}" required>
                            <button type="button" class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-slate-600"
                                    aria-label="Tampilkan password" aria-pressed="false"
                                    onclick="const i=document.getElementById('password');const show=i.type==='password';i.type=show?'text':'password';this.setAttribute('aria-pressed',show);this.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');">
                                <i data-feather="eye" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <div class="flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                            <div class="flex items-center mr-auto">
                                <input id="remember" name="remember" type="checkbox" class="form-check-input border mr-2" {{ old('remember') ? 'checked' : '' }}>
                                <label class="cursor-pointer select-none" for="remember">Ingat saya di perangkat ini</label>
                            </div>
                        </div>

                        <div class="mt-5 xl:mt-8 text-center xl:text-left">
                            <button type="submit" class="btn btn-primary py-3 px-4 w-full xl:w-40 align-top">Masuk</button>
                        </div>
                    </form>

                    <div class="intro-x mt-10 xl:mt-24 text-slate-600 dark:text-slate-500 text-center xl:text-left text-sm">
                        Sistem Informasi UKS &copy; {{ date('Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
