<?php

namespace App\Http\Controllers;

use App\Fcolor;
use App\Helpers\TranslitHelp;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;


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
		
		require_once(base_path().'/ya-autoload.php');
		$api = new \Yandex\Geo\Api();




		$f = fopen(storage_path().'/app/cards/zakaz.csv', 'r') or die('Ошибка загрузки файла заказов');
		$result = array();
		for($i=0;$data = fgetcsv($f, 1000,';'); $i++)
		{
			$num = count($data);
			for($c=0;$c<$num;$c++)
				$result[$i][$c] =  $data[$c];
		}

		fclose($f);


		$i = 0;
		foreach($result as $item){
			$api->setQuery('Санкт-Петербург, '.$item[0]);

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

			foreach ($collection as $addr) {
				$result[$i][4] = $addr->getLatitude().','.$addr->getLongitude();
				$result[$i][5] = $addr->getAddress();
			}
			$i++;
		}

		foreach ($result as $k => $res){
			if($color = Fcolor::where('restaurant', TranslitHelp::get($res[3]))->first()){
				$result[$k][6] = $color->color;
			}else
				$result[$k][6] = "#0000ff";
		}

		/*foreach ($result as $res){
			$ar_addr[] = $res[0];
		}
		$uniq_addr = array_unique($ar_addr);



		foreach ($uniq_addr as $res){
			$result_mod[]['address'] = $res;
		}

		foreach ($result_mod as $key => $res_mod){
			foreach ($result as $res){
				if($res_mod['address'] == $res[0]){
					$result_mod[$key]['items'][] = $res;
				}
			}
		}*/








		//echo '<pre>';
		//dd($result_mod);




		$user = Auth::user();
		return view('app.index', ['user' => $user, 'result' => $result]);
	}

	public function postWriteColor(Request $request){
		$color = $request['color'];
		$restaurant = $request['restaurant'];
		$fcolor = Fcolor::firstOrNew(['restaurant' => $restaurant]);
		$fcolor->color = $color;
		$fcolor->save();
		return $color . ' | ' . $restaurant;
	}

}
