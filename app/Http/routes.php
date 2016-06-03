<?php


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Redis;

Route::get('/',
	[
		'uses'	=> 'AppController@getHome',
		'as'	=> 'home'
	]
);

Route::post('/signin',
	[
		'uses'	=> 'UserController@postSignIn',
		'as'	=> 'signin'
	]
);

Route::get('/logout',
	[
		'uses'	=> 'UserController@getLogout',
		'as'	=> 'logout'
	]
);

Route::any('/app',
	[
		'uses'	=> 'AppController@getApp',
		'as'	=> 'app',
		'middleware'	=> 'auth'
	]
);

Route::any('/app/file',
	[
		'uses'	=> 'AppController@getAppFile',
		'as'	=> 'app-file',
		'middleware'	=> 'auth'
	]
);





Route::post('/app/write-color',
	[
		'uses'	=> 'AppController@postWriteColor',
		'as'	=> 'write-color',
		'middleware'	=> 'auth'
	]
);

Route::post('/app/read-color',
	[
		'uses'	=> 'AppController@postReadColor',
		'as'	=> 'read-color',
		'middleware'	=> 'auth'
	]
);

Route::post('/app/get-coord',
	[
		'uses'	=> 'AppController@postGetCoordinates',
		'as'	=> 'get-coord',
		'middleware'	=> 'auth'
	]
);


