@extends('layouts.master')

@section('content')
    <link rel="stylesheet" href="{{ asset('src/css/colorpicker/colorpicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('src/css/colorpicker/layout.css') }}" />
    <script src="{{ asset('src/js/colorpicker/colorpicker.js') }}"></script>
    <script src="{{ asset('src/js/colorpicker/eye.js') }}"></script>
    <script src="{{ asset('src/js/colorpicker/utils.js') }}"></script>
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
                        <h3>Фильтр по ресторанам</h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <? $filter_ar = []; ?>
                        @foreach($result as $val)
                            <?$filter_ar[] = $val[3];?>
                        @endforeach
                        <div id="filter">
                            <?$checkbox_ar = array_unique($filter_ar);?>
                            @foreach($checkbox_ar as $res)
                                @foreach($result as $res_color)
                                    @if($res_color[3] == $res)
                                        <?$color_filter = $res_color[6];?>
                                        @break
                                    @endif
                                @endforeach
                                @if($res)
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" id='{{\App\Helpers\TranslitHelp::get($res)}}' checked=true> {{$res}}
                                        </label>
                                        <div class="colorSelector {{\App\Helpers\TranslitHelp::get($res)}}" data-restoran="{{\App\Helpers\TranslitHelp::get($res)}}"><div style="background-color: {{$color_filter}}"></div></div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
            <div id="map"></div>
        </div>
    </div>

    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&mode=debug"></script>

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

            myMap.events.add('boundschange', function (event) {
                if (event.get('newZoom') != event.get('oldZoom')) {
                    console.log('Уровень масштабирования изменился' + event.get('newZoom'));
                    if(event.get('newZoom') >= 13){
                        objectManager.options.set({
                            groupByCoordinates: true
                        });
                        if($('.alert.alert-dismissible.alert-info').length == 0)
                            $('body').append('<div class="alert alert-dismissible alert-info" style="position: fixed; z-index: 99999; top:0%; left:0%; width: 320px;"><button type="button" class="close" data-dismiss="alert">×</button><b>ВКЛЮЧЕН точечный режим отображения заказов.</b></div>');
                        /*setTimeout(function () {
                            $('.alert.alert-dismissible.alert-info').fadeOut(1400);
                        }, 2000);*/
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
                //console.log(cluster);

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
                    //console.log(cache);
                }

                objectManager.clusters.setClusterOptions(e.get('objectId'), {
                    iconColor: is_color,
                    preset: 'islands#inverted'
                });

                if(is_color == "#000"){
                    cluster.properties.diff = true;
                }

                //console.log(cluster);


            });

            ///////////////////////////////

            // на событие клика по кластерам повесим формирование данных
            objectManager.clusters.events.add('click', function (e) {
                var clusterId = e.get('objectId'),
                cluster = objectManager.clusters.getById(clusterId);

                //console.log(cluster);

                var i = 0;
                var header; //Заголовок Кластера
                var count_order = cluster.properties.geoObjects.length; // кол-во заказов
                var clients = cluster.properties.clients; // кол-во клиентов
                var arRest = []; //массив ресторанов




                for (var i = 0; i < cluster.properties.geoObjects.length; i++){
                    //console.log(cluster.properties.geoObjects[i]);
                    //console.log('------------------------------');
                    //console.log('------------------------------');
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

                //var arClClient = [];

                for (var i = 0; i < cluster.properties.geoObjects.length; i++){
                    for(var j = 0; j < resRest.length; j++){
                        if(resRest[j].restaurant == cluster.properties.geoObjects[i].properties.restoran){
                            resRest[j].nameRestoran = cluster.properties.geoObjects[i].properties.nameRestoran;
                            resRest[j].color = cluster.properties.geoObjects[i].options.iconColor;
                            resRest[j].items.push(cluster.properties.geoObjects[i].properties.price);
                            //resRest[j].clients.push(cluster.properties.geoObjects[i].properties.hintContent);
                        }
                    }
                }


                var resSumm = 0;
                var bContent = (clients == 1) ? '<h4><strong>' + header + '</strong></h4>' : '<h4><strong>Общие данные</strong></h4>'
                bContent +=
                        '<div>' + '<strong>' + count_order + '</strong>' + ' заказов' + '</div>' +
                        '<div>' + '<strong>' + clients + '</strong>' + ' клиентов' + '</div>';
                bContent += '<ul>';

                for(var i = 0; i < resRest.length; i++){
                    var summ = 0;
                    var clients = unique(resRest[i].clients);
                    var countClients = clients.length;
                    //var arCountOrders =  [];
                    for(var j = 0; j < resRest[i].items.length; j++){
                        //console.log(resRest[i].items[j]);
                        summ += resRest[i].items[j];
                        //arCountOrders.push(resRest[i].items[j]);
                    }
                    var countOrders = j;
                    resSumm += summ; // общая сумма заказов
                    bContent += '<li>' + '<b>' + countOrders + '</b>' + ' заказов ' +'<b>' + summ + '</b>' + ' рублей ресторан ' + '<strong style="text-transform: uppercase; color:' + resRest[i].color + '">' + resRest[i].nameRestoran + '</strong>' + '</li>';
                }

                bContent += '</ul>';

                bContent += '<div>' + '<b>' + resSumm + '</b>' + ' рублей общая сумма заказов ' + '</div>';


                cluster.properties.balloonContent = bContent;

                objectManager.clusters.balloon.open(clusterId);

            });




            ////////////////////////////////////


            objectManager.add(
                    {
                        "type": "FeatureCollection",
                        "features": [
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
                                    //"balloonContent": "Содержимое балуна",
                                    "placemarkId": {{$i}},
                                    "restoran": "{{\App\Helpers\TranslitHelp::get($res[3])}}",
                                    "nameRestoran": "{{$res[3]}}",
                                    "clusterCaption": "{{$res[0]}} | №{{$i}}",
                                    "clusterHintContent": 'Группа объектов',
                                    "hintContent": "{{$res[0]}}",
                                    'iconContent': '1',
                                    'balloonContentHeader': '{{$res[0]}}',
                                    'balloonContentBody': '<ul><li>1 заказ</li><li>1 клиент</li><li>Сумма заказа: {{$res[1]}} рублей</li><li>Ресторан: {{$res[3]}}</li> </ul>',
                                    'balloonContentFooter': 'дата заказа: {{$res[2]}}',
                                    'price': {{$res[1]}}
                                },
                                "options": {
                                    "preset": "islands#icon",
                                    "iconColor": "{{$res[6]}}"
                                }
                            }
                            <?=($i !== (count($result) - 1)) ? ',' : '' ?>
                            <?$i++?>
                            @endforeach
                        ]
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


            /*console.log(objectManager.clusters.getAll());

            for (geometry in objectManager.clusters.getAll()){
                console.log(geometry);
            }*/




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
            @foreach($checkbox_ar as $res)
                $('.colorSelector.{{\App\Helpers\TranslitHelp::get($res)}}').ColorPicker({
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
                        $('.colorSelector.{{\App\Helpers\TranslitHelp::get($res)}} div').css('backgroundColor', '#' + hex);
                    },
                    onSubmit: function (hsb, hex, rgb) {
                        //$('.colorSelector.{{\App\Helpers\TranslitHelp::get($res)}} div').css('backgroundColor', '#' + hex);
                        //objectManager.objects.options.set('preset', 'islands#grayIcon');
                        settColor('#' + hex, '{{\App\Helpers\TranslitHelp::get($res)}}' );
                    }
                });
            @endforeach



            function settColor(col, rest) {
                objectManager.objects.each(function (object) {
                    //if(object.properties.restoran == el){
                    if(object.properties.restoran == rest) {
                        //var objectId = objectManager.objects.getById(object.id)
                        //objectManager.objects
                        //console.log(objectId);
                        /*objectManager.objects.options.set({
                            preset: 'islands#grayClusterIcons',
                            geoObjectIconColor: col
                        });*/
                        //myMap.geoObjects.objectManager.setObjectOptions({'iconColor': col});
                        //objectManager.objects.options.set({'iconColor': col});
                        //objectManager.objects.setObjectOptions(objectManager.objects.getById(object.id),{'iconColor': col});
                        //console.log(objectManager.objects.getById(object.id));

                        //object.options.set({iconColor: col});
                        objectManager.objects.setObjectOptions(object.id, {
                            iconColor: col
                        });


                        $.ajax({
                            type: 'POST',
                            url: '{{route('write-color')}}',
                            data: 'restaurant='+rest+'&color='+col+'&_token={{ csrf_token() }}',
                            success: function(data){
                                //console.log(data);
                                //$('.results').html(data);
                            }
                        });





                        /*console.log(object);
                        console.log(col);*/

                    }

                    //}
                    //console.log(object.properties.restoran == el);

                });
            }



            /*var arObj = [];
            var i = 0;
            objectManager.clusters.each(function (cluster) {
                var test;
                //console.log(cluster.properties.geoObjects);
                arObj[i] = new Array;
                for(var j = 0; j < cluster.properties.geoObjects.length; j++){
                    test = cluster.id;
                    var objGeo = {
                        'id': cluster.properties.geoObjects[j].id,
                        'color': cluster.properties.geoObjects[j].options.iconColor,
                        'cluster': cluster.id
                    };
                    arObj[i].push(objGeo);
                }

                var objGeo = {
                    'id': 99,
                    'color': "#fff",
                    'cluster': test
                };
                if(i==0){
                    arObj[i].push(objGeo);
                }
                i++;
            });


            arObj.forEach(function(item, i, arr) {
                var cache;
                var is_color;
                var cluster_id;
                for(var j = 0; j < item.length; j++){
                    cluster_id = item[j].cluster;
                    //если первый
                    if(j == 0){
                        cache = item[j].color;
                    }else{
                        //если остальные

                        //цвета совпадают
                        if(cache == item[j].color) {
                            is_color = item[j].color;
                        //цвета не совпадают
                        }else{
                            is_color = "#FFFFFF";
                            break;
                        }
                    }
                }
                //console.log(is_color + ' | ' + cluster_id);


                objectManager.clusters.setClusterOptions(cluster_id, {
                    iconColor: is_color
                });
            });*/





            /*var clusters = objectManager.clusters.getAll();*/
            //console.log(arObj);



        });

    </script>
@stop