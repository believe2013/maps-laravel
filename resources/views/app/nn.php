// Создаем собственный макет с информацией о выбранном геообъекте.
var customBalloonContentLayout = ymaps.templateLayoutFactory.createClass([
'<ul class=list>',
	// Выводим в цикле список всех геообъектов.
	'{% for geoObject in properties.geoObjects %}',
	'<li><a href="" data-placemarkid="{{ geoObject.properties.placemarkId }}" class="list_item">{{ geoObject.properties.balloonContentHeader|raw }}</a></li>',
	'{% endfor %}',
	'</ul>'
].join(''));