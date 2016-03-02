<?php

namespace App\Http\Controllers\Auth;

use App\ExternalRequest;
use App\GoogleUser;
use App\Tokens;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * @param  array $data
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
     * @param  array $data
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
     * Obtain the user information from Google.
     *
     * @param Request $request
     * @return Socialite ->user()
     */
    public function handleGoogleProviderCallback(Request $request)
    {
        if ($request->get('error') == "access_denied") {
            return redirect('/');
        }

        $authorizationCode = $request->get('code');

        $googleTokens = Tokens::getGoogleTokens($authorizationCode);
        //return var_dump($googleTokens);
        $user = Socialite::driver('google')->getUserByToken($googleTokens->access_token);
        $email = $user['emails'][0]['value'];
        $name = $user['name']['givenName'] . " " . $user['name']['familyName'];
        $avatar = $user['image']['url'];

        if (!GoogleUser::existsByEmailAndId($email, Auth::user()->id) && isset($googleTokens->refresh_token)) {
            GoogleUser::create([
                'user_id' => Auth::user()->id,
                'email' => $email,
                'names' => $name,
                'avatar' => $avatar,
                'googleAccessToken' => $googleTokens->access_token,
                'googleRefreshToken' => $googleTokens->refresh_token,
                'uriCode' => $authorizationCode,
                'expireValue' => $googleTokens->expires_in
            ]);
            return redirect('/availability/google')->with(['message' => 'You have linked this profile (' . $email . ')']);

        } elseif (GoogleUser::existsByEmailAndId($email, Auth::user()->id) && isset($googleTokens->refresh_token)) {
            $id = GoogleUser::where('email', '=', $email)->where('user_id', '=', Auth::user()->id)->select('id')->first()->pluck('id')['id'];
            GoogleUser::updateTokens($id, [
                'googleAccessToken' => $googleTokens->access_token,
                'googleRefreshToken' => $googleTokens->refresh_token,
                'uriCode' => $authorizationCode,
                'expireValue' => $googleTokens->expires_in
            ]);
            return redirect('/availability/google')->with(['message' => 'You already have this profile (' . $email . '). Data is updated.']);

        } elseif (GoogleUser::existsByEmailAndId($email, Auth::user()->id) && !isset($googleTokens->refresh_token)) {
            return redirect('/availability/google')->with(['message' => 'You already have this profile (' . $email . ')']);

        } elseif (!GoogleUser::existsByEmailAndId($email, Auth::user()->id) && !isset($googleTokens->refresh_token)) {
            return redirect('/availability/google')->withErrors(['Missing refresh token. Contact the administrator.']);

        } else {
            return var_dump($authorizationCode, $googleTokens, $user);
            abort(500, "Auth error. Contact the admin");
        }

        return redirect('/availability/google');

    }


}
