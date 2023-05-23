<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use phpseclib3\Crypt\RSA;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // protected function authenticated($request,$user){
    //     if ($user->isPatient()) {
    //         $privateKey=$user->private_key;
    //         $signature = $user->sign_public_key;
    //         $user_message_signature=$user->email;
    //         if (!$this->verifyPublicKeySignature($signature,$privateKey,$user_message_signature)) {
    //             Auth::logout();
    //             throw ValidationException::withMessages(['signature' => 'The public key signature is invalid..']);
    //         }
    //     }

    // }

    protected function verifyPublicKeySignature($signature,$privateKey,$message)
    {
        $private_key= RSA::loadPrivateKey($privateKey);
        return $private_key->getPublicKey()->verify($message, $signature);

    }
}
