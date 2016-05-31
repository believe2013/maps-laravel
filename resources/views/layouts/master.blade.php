<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Приложение - Карта заказов</title>
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-material-design/dist/css/bootstrap-material-design.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap-material-design/dist/css/ripples.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('src/css/main.css') }}"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
</head>
<body>
@if(Auth::check())
    @include('partials.nav')
@endif
<div class="container">
    @yield('content')
</div>

<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap-material-design/dist/js/ripples.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap-material-design/dist/js/material.min.js') }}"></script>
<script>
    $(function () {
        $.material.init();
    });
</script>
</body>
</html>