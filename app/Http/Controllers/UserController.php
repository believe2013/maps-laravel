<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends Controller
{
	//Вход
	public function postSignIn(Request $request)
	{
		$this->validate($request, [
			'email'			=> 'required',
			'password'		=> 'required',
		]);

		$remember = !!$request['remember_token'];

		if(Auth::attempt(['email' => $request['email'], 'password' => $request['password']], $remember))
		{
			return redirect()->route('app');
		}
		return redirect()->back();
	}

	//Выход
	public function getLogout()
	{
		Auth::logout();
		return redirect()->route('home');
	}
}
