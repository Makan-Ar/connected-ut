<?php namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Password Reset Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling password reset requests
	| and uses a simple trait to include this behavior. You're free to
	| explore this trait and override any methods you wish to tweak.
	|
	*/

	use ResetsPasswords;

    public $subject = "ConnectedUT - Password Rest";

	/**
	 * Create a new password controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct(Guard $auth, PasswordBroker $passwords)
	{
		$this->auth = $auth;
		$this->passwords = $passwords;

		$this->middleware('guest');
	}

    /**
     * Deletes a rest password link
     *
     * @param $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function cancelPasswordReset($token)
    {
        if (is_null($token))
            return redirect('/');

        $tokenRecord = DB::table('password_resets')->where('token', $token)->first();

        if($tokenRecord == null)
        {
            flash('error', 'Invalid!', 'This link is either not valid or has been expired!');
            return redirect('/');
        }

        DB::table('password_resets')->where('token', $token)->delete();

        flash('info', 'We Apologies!', 'The password rest link has been deactivated. Sorry for the inconvenience.');

        return redirect('/');
    }
}
