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
        return $this->belongsToMany('App\User');
    }

    public static function listAll()
    {
        return Company::select('id', 'name')->get();
    }

    public static function workers($company_id = null)
    {
        $company = Company::find($company_id);
        if($company == null) return false;

        return $company->users()->join('usersPersonalInfo as info', 'info.user_id', '=', 'users.id')->select('users.id', 'info.names as title')->get()->map(function($item) {return collect($item)->except('company_id');});
    }

}
