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

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function users_info()
    {
        return $this->hasManyThrough('App\PersonalInfo','App\User');
    }

    public static function listAll()
    {
        return Company::select('id', 'name')->get();
    }

    public static function workers($company_id = null)
    {
        $company = Company::find($company_id);
        if($company == null) return false;

        return $company->users_info()->select('users.id', 'names as title')->get()->map(function($item) {return collect($item)->except('company_id');});
    }

}
