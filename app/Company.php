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
        $company = self::find($company_id);
        if($company == null) return false;

        return DB::select("
            SELECT u.id, info.names as title
            FROM users u
            JOIN usersPersonalInfo info on info.user_id = u.id
            LEFT JOIN company_user cu ON cu.user_id = u.id
            WHERE cu.company_id = ?
        ", [$company_id]);
    }

}
