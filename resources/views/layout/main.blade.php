@extends('../layout/base')

@section('body')
    <body class="py-5">
        <div id="page-progress" class="page-progress" aria-hidden="true"></div>

        @yield('content')

        <!-- BEGIN: JS Assets-->
        <script src="{{ mix('dist/js/app.js') }}"></script>
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
