<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DoSomething.org')</title>

    <link rel="stylesheet" href="{{ asset('dist/app.css') }}">
    <script src="{{ asset('dist/modernizr.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="modernizr-no-js">
<div class="chrome">
    @if (session('status'))
        <div class="messages">{{ session('status') }}</div>
    @endif
    <div class="wrapper">
        <nav class="navigation">
            <a class="navigation__logo" href="http://www.dosomething.org"><span>DoSomething.org</span></a>
        </nav>
        @yield('content')
    </div>
</div>
</body>

<script src="{{ asset('/dist/app.js') }}"></script>

</html>