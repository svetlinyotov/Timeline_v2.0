<?php

namespace App\Http\Controllers\Auth;

use App\ExternalRequest;
use App\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
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

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $redirectPath = '/profile';
    protected $loginPath = '/login';

    /**
     * Create a new authentication controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return Socialite->redirect()
     */
    public function redirectToGoogleProvider()
    {
        return Socialite::driver('google')->scopes([
            'https://www.googleapis.com/auth/plus.me',
            'https://www.googleapis.com/auth/plus.profile.emails.read',
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.readonly'
            //access_type=offline&approval_prompt=force
        ])->redirect();
    }

    /**
     * @param $code
     * @return object {access_token: string, refresh_token:string, expires_in:int, id_token:string, token_type:string}
     */
    public function getGoogleTokens($code)
    {
        $url = 'https://www.googleapis.com/oauth2/v3/token';

        return ExternalRequest::POST($url, [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URL')
        ]);
    }

    /**
     * Obtain the user information from Google.
     *
     * @param Request $request
     * @return Socialite ->user()
     */
    public function handleGoogleProviderCallback(Request $request)
    {
        if($request->get('error') == "access_denied"){
            return redirect('/');
        }

        $user = Socialite::driver('google')->user();

        $authorizationCode = $request->get('code');

        $googleTokens = $this->getGoogleTokens($authorizationCode);


        return var_dump($authorizationCode, $googleTokens, $user);

    }
}
