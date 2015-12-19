<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currency';

    public function companies()
    {
        return $this->hasMany('App\Companies');
    }
}
