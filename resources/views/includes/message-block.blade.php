@if(count($errors) > 0)
    <div class="row">
        <div class="col-md-4 col-md-offset-4 error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(Session::has('message'))
    <div class="row">
        <div class="col-md-4 col-md-offset-4 success">
            {{Session::get('message')}}
        </div>
    </div>
@endif

@if(Session::has('message-error'))
    <div class="alert alert-dismissible alert-warning">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <h4>Ошибка!</h4>
        <h5>{{Session::get('message-error')}}</h5>
    </div>
@endif

@if(isset($nomap))
    <div class="alert alert-dismissible alert-warning">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <h4>Ошибка!</h4>
        <h5>{{$nomap}}</h5>
    </div>
@endif