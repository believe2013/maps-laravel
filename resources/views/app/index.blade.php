@extends('layouts.master')

@section('content')
    <link rel="stylesheet" href="{{ asset('src/css/colorpicker/colorpicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('src/css/colorpicker/layout.css') }}" />
    <script src="{{ asset('src/js/colorpicker/colorpicker.js') }}"></script>
    <script src="{{ asset('src/js/colorpicker/eye.js') }}"></script>
    <script src="{{ asset('src/js/colorpicker/utils.js') }}"></script>
    @include('includes.message-block')
    <div class="well bs-component">
        <form class="form-horizontal" method="post" action="{{route('app-file')}}" enctype="multipart/form-data">
            <div class="pull-right">
                <span class="label label-info" style="font-size: 16px;">Версия файла:</span><br><br>
                <p><b>Название:</b>
                    @if(!empty($file_info['name']))
                        {{$file_info['name']}}
                    @else
                        нет
                    @endif
                </p>
                <p><b>Добавлено:</b>
                    @if(!empty($file_info['created']))
                        {{$file_info['created']}}
                    @else
                        неизвестно
                    @endif
                </p>
            </div>

            <fieldset>

                <legend>Загрузить файл с заказами</legend>
                <blockquote style="border-left: 5px solid #f44336;">
                    <p>К загрузке принимаются файлы формата <span class="text-danger">CSV</span></p>
                    {{--<small>Someone famous in <cite title="Source Title">Source Title</cite></small>--}}
                </blockquote>
                <div class="form-group">
                    <label for="inputFile" class="col-md-2 control-label">Файл</label>

                    <div class="col-md-10">
                        <input type="text" readonly="" class="form-control" placeholder="Обзор ...">
                        <input type="file" id="inputFile" name="order-file">
                    </div>
                </div>
                {{csrf_field()}}
                <div class="form-group">
                    <div class="col-md-10 col-md-offset-2">
                        <button type="submit" class="btn btn-raised btn-primary">Загрузить</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>

    @if(!isset($nomap))

        <style>
            #map {
                width: 100%;
                height: 500px;
            }
        </style>

        <div id="progress-id" class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Загрузка объектов ...</h3>
            </div>
            <div class="panel-body">
                <br>
                <div id="prog" class="progress progress-striped active" style="height: 10px;">
                    <div class="progress-bar" style="width: 0%;"></div>
                </div>
            </div>
        </div>

        <div id="map-id" class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Карта с заказами</h3>
            </div>
            <div class="panel-body">
                <div class="form-group" style="margin-top: 0;">
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Фильтр по ресторанам</h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="filter">
                                @foreach($filter as $val)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" id='{{$val[1]}}' checked=true> {{$val[0]}}
                                        </label>
                                        <div class="colorSelector {{$val[1]}}" data-restoran="{{$val[1]}}"><div style="background-color: {{$val[2]}}"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wrap-map">
                    <div id="map"></div>
                </div>
            </div>
        </div>






        <script>

            $(document).ready(function () {

                // массив с объектами
                var resultGeo = [];
                        <?$i = 0;?>
                        @foreach($result as $res)

                var geoObj = {
                            'address'   : '{{$res[0]}}',
                            'price'     : {{$res[1]}},
                            'date'      : '{{$res[2]}}',
                            'name'      : '{{$res[3]}}',
                            'nameEn'    : '{{$res[4]}}',
                            'color'     : '{{$res[5]}}'
                            @if(isset($res[6]) or !empty($res[6]))
                            ,'coordinates': [{{$res[6]}}]
                            @else
                            ,'empty_id': {{$i}}
                            @endif
                        };


                resultGeo.push(geoObj);
                        <?$i++?>
                        @endforeach

                var noResultGeo = [];
                for(var i = 0; i < resultGeo.length; i++){
                    if(resultGeo[i].coordinates == undefined){
                        noResultGeo.push(resultGeo[i]);
                    }
                }
                if(noResultGeo.length > 0){
                    $('#map-id').css('display','none');
                }else{
                    $('#progress-id').css('display','none');
                    ymaps.ready(init);
                }


                var n = 0;                     //  set your counter to 1
                function myLoop (count) {           //  create a loop function
                    if(count == 0)
                        return;
                    console.log('n '+n);
                    var i = n;
                    setTimeout(function () {    //  call a 3s setTimeout when the loop is called

                        $.ajax({
                            type: 'POST',
                            async: false,
                            url: '{{route('get-coord')}}',
                            data: 'address=' + resultGeo[noResultGeo[i].empty_id].address + '&_token={{ csrf_token() }}',
                            success: function (data) {
                                console.log('Добавлен' + 'в базу' + ' №' + noResultGeo[i].empty_id);
                                resultGeo[noResultGeo[i].empty_id].coordinates = data;
                            }
                        });         //  your code here

                        var proc = Math.round(((n+1)/count)*100);

                        $('.progress-bar').css('width',proc+'%');

                        n++;                     //  increment the counter

                        if (n < count) {            //  if the counter < 10, call the loop function
                            myLoop(count);             //  ..  again which will trigger another
                        }                        //  ..  setTimeout()
                        else{
                            window.location.reload();
                        }



                    }, 4)


                }
                myLoop(noResultGeo.length);


                var myMap;
                function init () {

                    // Иниацилизация карты
                    myMap = new ymaps.Map('map', {
                        center: [59.958465, 30.319311],
                        zoom: 9,
                        behaviors: ['default', 'scrollZoom'],
                        controls: ["zoomControl", "fullscreenControl"]
                    }, {
                        searchControlProvider: 'yandex#search'
                    });

                    myMap.events.add('boundschange', function (event) {
                        if (event.get('newZoom') != event.get('oldZoom')) {
                            console.log('Уровень масштабирования изменился' + event.get('newZoom'));
                            if(event.get('newZoom') >= 15){
                                objectManager.options.set({
                                    groupByCoordinates: true
                                });
                                if($('.alert.alert-dismissible.alert-info').length == 0)
                                    $('body').append('<div class="alert alert-dismissible alert-info" style="position: fixed; z-index: 99999; top:0%; left:0%; width: 320px;"><button type="button" class="close" data-dismiss="alert">×</button><b>ВКЛЮЧЕН точечный режим отображения заказов.</b></div>');

                            }else{
                                objectManager.options.set({
                                    groupByCoordinates: false
                                });
                                $('.alert.alert-dismissible.alert-info').remove();
                            }
                        }
                    });


                    // Создаем собственный макет с информацией о выбранном геообъекте.
                    var customBalloonContentLayout = ymaps.templateLayoutFactory.createClass('@{{properties.balloonContent | raw}}');

                    var MyIconContentLayout = ymaps.templateLayoutFactory.createClass(
                            '<div style="color: #FFFFFF; font-weight: bold;">@{{properties.geoObjects.length}}</div>');

                    var objectManager = new ymaps.ObjectManager({
                        // Использовать кластеризацию.
                        clusterize: true,
                        gridSize: 200,
                        clusterDisableClickZoom: true,
                        //clusterOpenBalloonOnClick: true,
                        //clusterBalloonPanelMaxMapArea: 0,
                        //clusterBalloonMaxHeight: 200,

                        //groupByCoordinates: true
                        // Устанавливаем собственный макет контента балуна.
                    });


                    //функция проверки на уникальность
                    function unique(arr) {
                        var result = [];
                        nextInput:
                                for (var i = 0; i < arr.length; i++) {
                                    var str = arr[i]; // для каждого элемента
                                    for (var j = 0; j < result.length; j++) { // ищем, был ли он уже?
                                        if (result[j] == str) continue nextInput; // если да, то следующий
                                    }
                                    result.push(str);
                                }
                        return result;
                    }


                    // на событие добавление кластеров повесим цзменение цвета
                    objectManager.clusters.events.add('add', function (e) {
                        var cluster = objectManager.clusters.getById(e.get('objectId'));

                        var arObj = [];
                        var arClient = [];

                        for(var i = 0; i < cluster.properties.geoObjects.length; i++) {
                            var objGeo = {
                                'id': cluster.properties.geoObjects[i].id,
                                'color': cluster.properties.geoObjects[i].options.iconColor,
                                'cluster': cluster.id
                            };
                            arObj.push(objGeo);

                            //формируем клиентов
                            arClient.push(cluster.properties.geoObjects[i].properties.hintContent);
                        }

                        // получаем клиентов на кластер и добавляем в него
                        var arUnClient = unique(arClient);
                        var countClient = arUnClient.length;
                        cluster.properties.clients = countClient;


                        var cache;
                        var is_color;
                        var is_fill = false;

                        for(var j = 0; j < arObj.length; j++){
                            //console.log(arObj);
                            cluster_id = arObj[j].cluster;
                            //если первый
                            if(j == 0){
                                cache = arObj[j].color;
                            }else{
                                //если остальные

                                //цвета совпадают
                                if(cache == arObj[j].color) {
                                    is_color = arObj[j].color;
                                    cache = arObj[j].color;
                                    //цвета не совпадают
                                }else{
                                    is_color = "#FFFFFF";
                                    break;
                                }
                            }
                        }

                        objectManager.clusters.setClusterOptions(e.get('objectId'), {
                            iconColor: is_color,
                            preset: 'islands#inverted'
                        });

                        if(is_color == "#000"){
                            cluster.properties.diff = true;
                        }



                    });

                    ///////////////////////////////

                    // на событие клика по кластерам повесим формирование данных
                    objectManager.clusters.events.add('click', function (e) {
                        var clusterId = e.get('objectId'),
                                cluster = objectManager.clusters.getById(clusterId);

                        var i = 0;
                        var header; //Заголовок Кластера
                        var count_order = cluster.properties.geoObjects.length; // кол-во заказов
                        var clients = cluster.properties.clients; // кол-во клиентов
                        var arRest = []; //массив ресторанов




                        for (var i = 0; i < cluster.properties.geoObjects.length; i++){
                            if(i == 0){
                                header = cluster.properties.geoObjects[i].properties.balloonContentHeader;
                            }
                            arRest.push(cluster.properties.geoObjects[i].properties.restoran)

                        }

                        // массив уникальных значений ресторанов
                        var arUnRest = unique(arRest);

                        var resRest = [];

                        for(var i = 0; i < arUnRest.length; i++){
                            resRest.push({
                                'restaurant': arUnRest[i],
                                'items': [],
                                'clients': []
                            })
                        }

                        for (var i = 0; i < cluster.properties.geoObjects.length; i++){
                            for(var j = 0; j < resRest.length; j++){
                                if(resRest[j].restaurant == cluster.properties.geoObjects[i].properties.restoran){
                                    resRest[j].nameRestoran = cluster.properties.geoObjects[i].properties.nameRestoran;
                                    resRest[j].color = cluster.properties.geoObjects[i].options.iconColor;
                                    resRest[j].items.push(cluster.properties.geoObjects[i].properties.price);
                                }
                            }
                        }


                        var resSumm = 0;
                        var bContent = (clients == 1) ? '<h4><strong>' + header + '</strong></h4>' : '<h4><strong>Общие данные</strong></h4>'
                        bContent +=
                                '<div>' + ' Заказы: ' + '<strong>' + count_order + '</strong>' + '</div>' +
                                '<div>' + ' Клиенты: ' + '<strong>' + clients + '</strong>' + '</div>';
                        bContent += '<ul>';

                        for(var i = 0; i < resRest.length; i++){
                            var summ = 0;
                            var clients = unique(resRest[i].clients);
                            var countClients = clients.length;
                            //var arCountOrders =  [];
                            for(var j = 0; j < resRest[i].items.length; j++){
                                summ += resRest[i].items[j];
                            }
                            var countOrders = j;
                            resSumm += summ; // общая сумма заказов
                            //формат суммы
                            summ = summ.toString();
                            summ = summ.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                            bContent += '<li>' + 'ресторан ' + '<strong style="text-transform: uppercase; color:' + resRest[i].color + '">' + resRest[i].nameRestoran + '</strong>,' + ' заказы: ' + '<b>' + countOrders + '</b> на сумму ' +'<b>' + summ + '</b>' + ' руб.</li>';
                        }

                        bContent += '</ul>';
                        //формат суммы
                        resSumm = resSumm.toString();
                        resSumm = resSumm.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                        bContent += '<div>' + '<b>' + resSumm + '</b>' + ' рублей общая сумма заказов ' + '</div>';


                        cluster.properties.balloonContent = bContent;

                        objectManager.clusters.balloon.open(clusterId);

                    });




                    ////////////////////////////////////

                    var objectManagerGeo = [];
                    for(var i = 0; i < resultGeo.length; i++){
                        var objManager  = {
                            "type": "Feature",
                            "id": i,
                            "geometry": {
                                "type": "Point",
                                "coordinates": resultGeo[i].coordinates
                            },
                            "properties": {
                                "placemarkId": i,
                                "restoran": resultGeo[i].nameEn,
                                "nameRestoran": resultGeo[i].name,
                                "clusterCaption": resultGeo[i].address + ' | №' + i,
                                "clusterHintContent": 'Группа объектов',
                                "hintContent": resultGeo[i].address,
                                'iconContent': '1',
                                'balloonContentHeader': resultGeo[i].address,
                                'balloonContentBody': '<ul><li>Заказы: 1</li><li>Клиенты: 1</li><li>Сумма заказа: ' + resultGeo[i].price.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ') + ' рублей</li><li>Ресторан: ' + resultGeo[i].name +'</li></ul>',
                                'balloonContentFooter': 'дата заказа: ' + resultGeo[i].date,
                                'price': resultGeo[i].price
                            },
                            "options": {
                                "preset": "islands#icon",
                                "iconColor": resultGeo[i].color
                            }
                        };
                        objectManagerGeo.push(objManager);
                    }


                    objectManager.add(
                            {
                                "type": "FeatureCollection",
                                "features": objectManagerGeo
                            }
                    );

                    //objectManager.clusters.hint.setData('Хинт');



                    objectManager.clusters.options.set({
                        clusterIconColor: 'red',

                        clusterIconContentLayout: ymaps.templateLayoutFactory.createClass(
                                '<div style="color: #000; font-weight: bold;">$[properties.geoObjects.length]</div>'),
                        balloonContentLayout: customBalloonContentLayout,
                        clusterOpenBalloonOnClick: false
                    });

                    myMap.geoObjects.add(objectManager);



                    myMap.setBounds(objectManager.getBounds(), {
                        checkZoomRange: true
                    });



                    $('#filter input').click(function () {
                        var arr_filter = new Array;
                        $('#filter input').each(function (index, element) {
                            if($(element).is(':checked')){
                                arr_filter[arr_filter.length] = $(element).prop('id');
                            }
                        });
                        var str = '';
                        for(var i = 0; i < arr_filter.length; i++){
                            if(i < (arr_filter.length - 1))
                                str = str + 'properties.restoran == "' + arr_filter[i] + '" || ';
                            else
                                str = str + 'properties.restoran == "' + arr_filter[i] + '"';

                        }
                        if(!str)
                            objectManager.setFilter('properties.restoran == "erunda-chtobi-bilo-pusto"');
                        else
                            objectManager.setFilter(str);
                    });

                    objectManager.objects.options.set('preset', 'islands#grayIcon');

                    // Задание цвета
                    @foreach($filter as $res)
                        $('.colorSelector.{{$res[1]}}').ColorPicker({
                        color: '#0000ff',
                        onShow: function (colpkr) {
                            $(colpkr).fadeIn(500);
                            return false;
                        },
                        onHide: function (colpkr) {
                            $(colpkr).fadeOut(500);
                            return false;
                        },
                        onChange: function (hsb, hex, rgb) {
                            $('.colorSelector.{{$res[1]}} div').css('backgroundColor', '#' + hex);
                        },
                        onSubmit: function (hsb, hex, rgb) {
                            //$('.colorSelector.{{$res[1]}} div').css('backgroundColor', '#' + hex);
                            //objectManager.objects.options.set('preset', 'islands#grayIcon');
                            settColor('#' + hex, '{{$res[1]}}' );
                        }
                    });
                    @endforeach



                    function settColor(col, rest) {
                        objectManager.objects.each(function (object) {
                            if(object.properties.restoran == rest) {
                                objectManager.objects.setObjectOptions(object.id, {
                                    iconColor: col
                                });

                                $.ajax({
                                    type: 'POST',
                                    url: '{{route('write-color')}}',
                                    data: 'restaurant='+rest+'&color='+col+'&_token={{ csrf_token() }}',
                                    success: function(data){
                                        //console.log(data);
                                    },
                                    error: function (jqXHR, exception) {
                                        console.log(jqXHR);
                                        // Your error handling logic here..
                                    }
                                });


                            }
                        });
                    }

                }
            });

        </script>
    @endif
@stop