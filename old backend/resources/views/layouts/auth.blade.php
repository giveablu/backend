<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.gstatic.com/" rel="preconnect">
    <link href="{{ asset('panel/img/icons/icon-48x48.png') }}" rel="shortcut icon" />

    <title>{{ config('app.name') }}</title>

    <link href="{{ asset('panel/css/light.css') }}" rel="stylesheet">
    <style>
        body {
            opacity: 0;
        }
    </style>
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">
    <main class="d-flex w-100 h-100">
        <div class="d-flex flex-column container">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 d-table h-100 mx-auto">
                    @yield('auth-section')
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('panel/js/app.js') }}"></script>
    <script src="{{ asset('panel/js/axios.min.js') }}"></script>
    @stack('auth-script')
</body>

</html>
