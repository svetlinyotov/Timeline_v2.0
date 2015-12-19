<?php

namespace App\Http\Controllers;

use App\Common;
use App\Http\Requests;
use Illuminate\Http\Request;

class CommonController extends Controller
{

    public function timezone()
    {
        return Common::timezone();
    }

}
