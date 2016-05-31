@extends('layouts.master')

@section('content')
    <div class="jumbotron col-sm-6 col-sm-push-3" style="margin-top: 5em">
        <h1>Вход в приложение <i style="font-size: 50px; vertical-align: middle; color:#009688;" class="material-icons pull-right">input</i></h1>

        <form action="{{ route('signin') }}" method="POST">
            <div class="form-group">
                <input type="text" id="email" name="email" placeholder="Введите эл. почту" class="form-control"/>
            </div>
            <div class="form-group">
                <input type="text" id="password" name="password" placeholder="Введите пароль" class="form-control"/>
            </div>
            <div class="form-group" style="margin-top: 0;">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember_token"> Запомнить меня
                    </label>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" id="login-btn" name="login" value="Войти" class="btn btn-primary"/>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
        </form>
    </div>
@stop
