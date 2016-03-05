<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class GoogleUser extends Model
{
    protected $table = 'users_linked_google_profiles';

    protected $fillable = ['user_id', 'email', 'names', 'avatar', 'googleAccessToken', 'googleRefreshToken', 'uriCode', 'expireValue'];
    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function calendars()
    {
        return $this->hasMany('App\GoogleUserCalendar', 'user_id');
    }

    public static function getUserData($id)
    {
        return self::select('id', 'user_id', 'email', 'names', 'avatar')->where('user_id', '=', $id)->get();
    }

    public static function getUserByGoogleId($id)
    {
        return self::select('id', 'user_id', 'email', 'names', 'avatar')->where('id', '=', $id)->first();
    }

    public static function existsByEmailAndId($email, $id)
    {
        return self::where('email', '=', $email)->where('user_id', '=', $id)->count();
    }

    /**
     * @param string $email
     * @param string $token = access | refresh
     * @return string token value
     */
    public static function getTokenByEmail(string $email, string $token) : string
    {
        $_token = 'google'.ucfirst($token).'Token';
        return self::where('email', '=', $email)->select($_token)->first()->pluck($_token)["$_token"];
    }

    /**
     * @param int $id
     * @param string $token = access | refresh
     * @return string token value
     */
    public static function getTokenById(int $id, string $token = "access") : string
    {
        $_token = 'google'.ucfirst($token).'Token';
        $data = self::where('id', '=', $id)->select('user_id', $_token)->first();

        if(Auth::user()->role == "worker" and $data->user_id != Auth::user()->id)
            abort(401, "Unauthorized request for this session");

        return $data->$_token;
    }

    /**
     * @param int $id
     * @param array $data = [googleAccessToken, googleRefreshToken, uriCode, expireValue]
     * @return bool
     */
    public static function updateTokens(int $id, Array $data) : bool
    {
        $_data = [];
        if (array_key_exists('googleAccessToken', $data)) {
            $_data['googleAccessToken'] = $data['googleAccessToken'];
        }
        if (array_key_exists('googleRefreshToken', $data)){
            $_data['googleRefreshToken'] = $data['googleRefreshToken'];
        }
        if (array_key_exists('uriCode', $data)) {
            $_data['uriCode'] = $data['uriCode'];
        }
        if (array_key_exists('expireValue', $data)) {
            $_data['expireValue'] = $data['expireValue'];
        }

        self::where('id', '=', $id)->update($_data);

        return true;

    }

    public static function getCalendarsByUser($user_id)
    {
        $token = self::getTokenById($user_id);
        return ExternalRequest::GET('https://www.googleapis.com/calendar/v3/users/me/calendarList', $user_id, $token);
    }
}
