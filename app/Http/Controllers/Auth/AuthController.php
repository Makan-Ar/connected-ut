<?php namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Validator;
use create;

class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	use AuthenticatesAndRegistersUsers;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data)
	{
		return User::create([
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			'active' => false,
			'contact_email' => $data['email']
		]);
	}

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * If you change the password validation, change it on ChangePasswordRequest too.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
//	public function validator(array $data)
//	{
//		return Validator::make($data, [
//			'email' => 'required|email|max:255|unique:users|regex:/.+@rockets.utoledo.edu/',
//			'password' => 'required|min:6|confirmed',
//		]);
//	}
}
