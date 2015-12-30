<?php

namespace App;

class Common
{
    public static function timezone()
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    public static function formatDateTimeForSQL($time) : string
    {
        return date('Y-m-d H:i:s', strtotime($time));
    }

    public static function formatDateTimeFromSQL($time) : string
    {
        return date('jS M Y, h:iA', strtotime($time));
    }

    public static function formatTimeForSQL($time) : string
    {
        return date('H:i:s', strtotime($time));
    }

    public static function formatTimeFromSQL($time) : string
    {
        return date('h:i A', strtotime($time));
    }

    public static function formatTimeFromSQL24($time) : string
    {
        return date('H:i', strtotime($time));
    }

    public static function isInTheFuture($time) : bool
    {
        return (strtotime($time) - strtotime("now")) > 0;
    }

    public static function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if(!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    public static function randString($length)
    {
        $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $char = str_shuffle($char);
        for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
            $rand .= $char{mt_rand(0, $l)};
        }
        return "".$rand;
    }

    public static function timeAgo($time)
    {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");

        $now = time();

        $difference     = $now - strtotime($time);
        $tense         = "ago";

        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if($difference != 1) {
            $periods[$j].= "s";
        }

        if($j == 0){
            return "just now";
        }else {
            return "$difference $periods[$j] ago";
        }
    }
}
