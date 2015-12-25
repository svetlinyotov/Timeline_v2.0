<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $table = 'companies';

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function holidays()
    {
        return $this->hasMany('App\CompanyHolidays');
    }

}
