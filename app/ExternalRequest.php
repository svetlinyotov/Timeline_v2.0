<?php

namespace App;

use \Exception;

class ExternalRequest
{
    /**
     * @param $url
     * @param array $params
     * @return json
     */
    public static function POST($url, Array $params)
    {
        $POST_params = "";
        foreach ($params as $key => $param) {
            $POST_params .= "&{$key}={$param}";
        }

        $init = curl_init($url);
        curl_setopt($init, CURLOPT_POST, 1);
        curl_setopt($init, CURLOPT_POSTFIELDS, $POST_params);
        curl_setopt($init, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($init, CURLOPT_HEADER, 0);
        curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);

        return json_decode(curl_exec($init));
    }

    /**
     * @param $url
     * @param null $user_id
     * @param null $token
     * @return json
     */
    public static function GET($url, $user_id = null, $token = null)
    {
        try {
            $ch = curl_init();

            if (FALSE === $ch)
                throw new Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if($token != null){
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , "Authorization: Bearer ".$token ));
            }

            $content = curl_exec($ch);

            if (FALSE === $content)
                throw new Exception(curl_error($ch), curl_errno($ch));

        } catch(Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);

        }

        $json = json_decode($content);

        if(isset($json -> error )) {
            if ($json->error->code == 401) {
                $new_access_token = Tokens::refreshToken($user_id);

                $query = parse_url($url, PHP_URL_QUERY);

                parse_str($query, $val);
                $val['access_token'] = $new_access_token;
                $fixed_query = http_build_query($val);

                $_url = parse_url($url);
                $_url['query'] = $fixed_query;

                $new_url = file_get_contents(Common::http_build_url($url, $_url));

                $json = json_decode($new_url);

            } else {
                abort(500, $json->error->message);
            }
        }

        return $json;
    }
}
