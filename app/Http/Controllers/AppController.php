<?php

namespace App\Http\Controllers;

use App\Coordinates;
use App\Fcolor;
use App\FileOrder;
use App\Helpers\TranslitHelp;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Cache;
use Jenssegers\Date\Date;


class AppController extends Controller
{
	public function getHome()
	{
		if(Auth::check())
			return redirect()->route('app');
		else
			return view('login');
	}

	public function getApp()
	{

		if(!file_exists(storage_path('app/cards-one/order-map.csv' ))){
			$user = Auth::user();
			return view('app.index', ['user' => $user, 'nomap' => 'Еще не загружено ни одного файла для отображения карты.']);
		}

		$f = fopen(storage_path('app/cards-one/order-map.csv'), 'r') or die('Ошибка загрузки файла заказов.');

		$result = array();

		for($i=0;$data = fgetcsv($f, 0,';'); $i++)
		{
			$num = count($data);
			for($c=0;$c<$num;$c++) {

				$result[$i][$c] = str_replace('"', '', $data[$c]);

				//Боремся с некорректными символами в адресе
				$cacheAdress = str_replace(['/','\\'], '-', $data[0]);
				$cacheAdress = str_replace(['"'], '', $cacheAdress);
				$result[$i][0] = $cacheAdress;

				// Цена
				if(preg_match ( '/^[0-9 ]*/' , trim($data[1]), $matches)){
					$result[$i][1] = str_replace(' ', '', $matches[0]);
				}
				//если цена пустая
				if(empty($result[$i][1])){
					$result[$i][1] = 0;
				}
			}

		}
		fclose($f);

		//dd($result[1871]);

		$coordinates = Coordinates::all();
		if(count($coordinates)){
			foreach ($coordinates as $k => $v){
				$arCoord[$v->address] = $v->coordinates;
			}
		}else
			$arCoord = [];


		foreach ($result as $key => $value){
			foreach ($arCoord as $k => $v){
				if($k == $value[0]){
					$result[$key][6] = $v;
				}
			}
		}

		// Уникальный массив ресторанов
		// Запись транслита ресторана в массив
		$arRestaurant = [];
		foreach ($result as $k => $res){
			$result[$k][4] = TranslitHelp::get($res[3]);
			$arRestaurant[] = $res[3];
		}
		$arRestaurant = array_unique($arRestaurant);

		// Уникальный массив ресторанов - цветов
		$arResColor = [];
		// Для фильтра во views
		$filterRes = [];
		foreach ($arRestaurant as $value){
			$restTranslit = TranslitHelp::get($value);
			if($rest = Fcolor::where('restaurant', $restTranslit)->first()){
				$arResColor[$value] = $rest->color;
				$filterRes[] = [$value, $restTranslit, $rest->color];
			}else {
				$arResColor[$value] = "#0000ff";
				$filterRes[] = [$value, $restTranslit, "#0000ff"];
			}
		}

		// заносим данные о цвете в массив
		foreach ($result as $key => $value){
			foreach ($arResColor as $k => $v){
				if($value[3] == $k){
					$result[$key][5] = $v;
				}
			}
		}

		Date::setLocale('ru');
		// Инфа о последнем загруженном файле
		$file_info = FileOrder::orderBy('id', 'desc')->first();
		$f_info = [
			'name' => $file_info->name,
			'created' => Date::parse($file_info->created_at)->diffForHumans(),
		];
		//dd(Date::parse($file_info->created_at)->diffForHumans());

		$user = Auth::user();
		return view('app.index', ['user' => $user, 'result' => $result, 'filter' => $filterRes, 'file_info' => $f_info]);
	}

	public function getAppFile(Request $request)
	{
		if ($request->hasFile('order-file'))
		{
			$file = $request->file('order-file');

			$nameFile =$file->getClientOriginalName();

			$ext = $file->getClientOriginalExtension();
			if($ext != 'csv')
				return redirect()->back()->with(['message-error' => 'Файл формата '.$ext. ' не соответствует данному приложению.']);


			// если кодировка windows 1251
			$arFile = file($file);
			//dd($arFile);
			if(mb_detect_encoding($arFile[0], 'ASCII,UTF-8,ISO-8859-15') != 'UTF-8'){
				$f = fopen (storage_path('app/cards-one/order-map.'.$ext), "w");
				foreach ($arFile as $k => $arLine){
					$tmpLine = iconv("WINDOWS-1251", "UTF-8", $arLine);
					fwrite($f, trim($tmpLine)."\r\n");
				}
				fclose($f);
				// добавление инфы о загруженном файле
				$fileOrder = new FileOrder();
				$fileOrder->name = $nameFile;
				$fileOrder->save();
				return redirect()->back()->with(['message' => 'Добавлены новые объекты (win-1251)']);
			}


			// если кодировка utf-8
			$filename = 'order-map.'.$ext;

			// move the file to correct location
			if(!file_exists(storage_path('app/cards-one'))){
				mkdir(storage_path('app/cards-one'), 0777, true);
			}
			$file->move(storage_path('app/cards-one'), $filename);
			// добавление инфы о загруженном файле
			$fileOrder = new FileOrder();
			$fileOrder->name = $nameFile;
			$fileOrder->save();
			return redirect()->back()->with(['message' => 'Добавлены новые объекты (utf-8)']);
		}
		return redirect()->back()->with(['message-error' => 'Загруженные данные не являются файлом.']);
	}
	
	

	public function postWriteColor(Request $request){
		$color = $request['color'];
		$restaurant = $request['restaurant'];
		$fcolor = Fcolor::firstOrNew(['restaurant' => $restaurant]);
		$fcolor->color = $color;
		$fcolor->save();
		return $color . ' | ' . $restaurant;
	}
	

	public function postGetCoordinates(Request $request){
		require_once(base_path().'/ya-autoload.php');
		$api = new \Yandex\Geo\Api();

		$address = $request['address'];


		$api->setQuery('Санкт-Петербург, '.$address);
		// Настройка фильтров
		$api
			->setLimit(1) // кол-во результатов
			->setLang(\Yandex\Geo\Api::LANG_RU) // локаль ответа
			->load();
		$response = $api->getResponse();
		$response->getFoundCount(); // кол-во найденных адресов
		$response->getQuery(); // исходный запрос
		$response->getLatitude(); // широта для исходного запроса
		$response->getLongitude(); // долгота для исходного запроса
		$collection = $response->getList();
		foreach ($collection as $item) {
			$coordinates = $item->getLatitude().','.$item->getLongitude();
		}

		$coord = new Coordinates();
		$coord->address = $address;
		$coord->coordinates = $coordinates;
		$coord->save();
		$coordinates = '['.$coordinates.']';
		return $coordinates;
	}

}
