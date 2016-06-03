<div class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            {{--<a class="navbar-brand" href="javascript:void(0)">Brand</a>--}}
        </div>
        <div class="navbar-collapse collapse navbar-responsive-collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="{{ route('app') }}">Приложение</a></li>
                {{--<li class="dropdown">
                    <a href="javascript:void(0)" data-target="#" class="dropdown-toggle" data-toggle="dropdown">Управление пользователями
                        <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="javascript:void(0)">Все пользователи</a></li>
                        <li class="divider"></li>
                        <li><a href="javascript:void(0)">Добавить нового пользователя</a></li>
                    </ul>
                </li>--}}
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a>Статус: администратор</a></li>
                <li class="dropdown">
                    <a href="#" data-target="#" class="dropdown-toggle" data-toggle="dropdown"><i class="material-icons" style="margin: -4px 10px 0 16px;">person</i> {{Auth::user()->name}}
                        <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        {{--<li><a href="javascript:void(0)">Профиль</a></li>
                        <li class="divider"></li>--}}
                        <li><a href="{{ route('logout') }}">Выход</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
