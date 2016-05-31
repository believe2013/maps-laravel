@extends('layouts.master')

@section('content')
    <div class="well bs-component">
        <form class="form-horizontal">
            <fieldset>
                <legend>Загрузить файл с заказами</legend>

                <div class="form-group">
                    <label for="inputFile" class="col-md-2 control-label">Файл</label>

                    <div class="col-md-10">
                        <input type="text" readonly="" class="form-control" placeholder="Обзор ...">
                        <input type="file" id="inputFile" multiple="">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-10 col-md-offset-2">
                        <button type="submit" class="btn btn-raised btn-primary">Загрузить</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    {{--<pre>
        {{print_r($result)}}
    </pre>--}}

    <style>
        #map {
            width: 100%;
            height: 500px;
        }
    </style>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Карта с заказами</h3>
        </div>
        <div class="panel-body">
            <div class="form-group" style="margin-top: 0;">
                <div class="row">
                    <div class="col-md-12">
                        <h3>Фильтр по цветам</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <? $filter_ar = []; ?>
                        @foreach($result as $val)
                            <?$filter_ar[] = $val[3];?>
                        @endforeach
                        <div class="checkbox" id="filter">
                            <?$checkbox_ar = array_unique($filter_ar);?>
                            @foreach($checkbox_ar as $res)
                                @if($res)
                                    <label>
                                        <input type="checkbox" id='{{\App\Helpers\TranslitHelp::get($res)}}' checked=true> {{$res}}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div id="map"></div>
        </div>
    </div>

    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>

    <script>

        ymaps.ready(function () {

            // Иниацилизация карты
            var myMap = new ymaps.Map('map', {
                        center: [59.958465, 30.319311],
                        zoom: 9,
                        behaviors: ['default', 'scrollZoom'],
                        controls: ["zoomControl", "fullscreenControl"]
                    }, {
                        searchControlProvider: 'yandex#search'
                    });



            // Создадим объекты из их JSON-описания и добавим их на карту.
            window.myObjects = ymaps.geoQuery({
                type: "FeatureCollection",
                features: [
                        <?$i = 0?>
                        @foreach($result as $res)
                    {
                        "type": "Feature",
                        "id": {{$i}},
                        "geometry": {
                            "type": "Point",
                            "coordinates": [{{$res[4]}}]
                        },
                        "properties": {
                            "placemarkId": {{$i}},
                            "restoran": "{{\App\Helpers\TranslitHelp::get($res[3])}}",
                            "clusterCaption": "{{$res[0]}} | №{{$i}}",
                            "hintContent": "{{$res[0]}}",
                            'iconContent': '1',
                            'balloonContentHeader': '{{$res[0]}}',
                            'balloonContentBody': '<ul><li>1 заказ</li><li>1 клиент</li><li>Сумма заказа: {{$res[1]}} рублей</li><li>Ресторан: {{$res[3]}}</li> </ul>',
                            'balloonContentFooter': 'дата заказа: {{$res[2]}}'
                        },
                        "options": {
                            "preset": "islands#icon",
                            "iconColor": "#B51EFF"
                        }
                    }
                    <?=($i !== (count($result) - 1)) ? ',' : '' ?>
                    <?$i++?>
                    @endforeach
                ]
            }).addToMap(myMap);



        });
    </script>
@stop