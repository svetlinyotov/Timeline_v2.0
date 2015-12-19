<?php

namespace App;

class Common
{
    public static function timezone()
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }
}
