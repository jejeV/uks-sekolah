@extends('../layout/base')

@section('body')
    <body class="login">
        <div id="page-progress" class="page-progress" aria-hidden="true"></div>
        @yield('content')

        <!-- BEGIN: JS Assets-->
        <script src="{{ mix('dist/js/app.js') }}"></script>
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
