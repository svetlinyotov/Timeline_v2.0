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
}
