<?php

namespace App;


class Tokens
{

    /**
     * @param $code
     * @return object{access_token: string, refresh_token:string, expires_in:int, id_token:string, token_type:string}
     */
    public static function getGoogleTokens($code)
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

    public static function refreshToken($id)
    {
        $refresh_token = GoogleUser::getTokenById($id, "refresh");

        $url = 'https://www.googleapis.com/oauth2/v3/token';

        $response = ExternalRequest::POST($url, [
            'grant_type' => 'refresh_token',
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'refresh_token' => $refresh_token
        ]);

        if(isset($response -> error)) {
            return false;
        }

        GoogleUser::updateTokens($id,
            [
                'googleAccessToken' => $response->access_token,
                'expireValue' => $response->expires_in
            ]);

        return $response->access_token;
    }

    public static function revokeToken($token)
    {
        $url = 'https://accounts.google.com/o/oauth2/revoke';

        return ExternalRequest::POST($url, [
            'token' => $token
        ]);
    }
}
