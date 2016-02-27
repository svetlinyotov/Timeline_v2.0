<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\DB;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['names', 'email', 'password', 'last_login'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function info()
    {
        return $this->hasOne('App\PersonalInfo');
    }

    public function company()
    {
        return $this->belongsToMany('App\Company');
    }

    public function rosters()
    {
        return $this->belongsToMany('App\Roster');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification');
    }

    public function messages()
    {
        return $this->hasMany('App\Messages');
    }

    public static function notLinkedCompanies($user_id)
    {
        return DB::select("
            SELECT id, name
            FROM companies
            WHERE id NOT IN (
                SELECT company_id
                FROM company_user
                WHERE user_id = ?
            )
        ", [$user_id]);
    }

    public static function events($user_id, $data)
    {
        return DB::select("
          SELECT
          rosters.id as id,
          ru.user_id as resourceId,
          rosters.start_time as start,
          rosters.end_time as end,
          rosters.name as title,
          rosters.other as description,
          rosters.address as address,
          rosters.coordinates as coordinates,
          c.id as comapny_id,
          c.name as comapny_name,
          ru.is_supervisor as supervisor,
          ru.status as status,
          u.email as user,
          CASE
              WHEN ru.status = 'pending' OR ru.status = '' THEN 'color-gray'
              WHEN ru.status = 'accepted' THEN 'color-green'
              WHEN ru.status = 'declined' THEN 'color-red'
              WHEN ru.status = 'canceled' THEN 'color-white'
          END as className
          FROM rosters
          left JOIN roster_user ru ON ru.roster_id = rosters.id
          LEFT JOIN users u ON u.id = ru.user_id
          LEFT JOIN companies c ON c.id = rosters.company_id
          WHERE
            u.id = ?
            AND (start_time BETWEEN ? and ? OR end_time BETWEEN ? and ?)
          ", [$user_id, $data['start'], $data['end'], $data['start'], $data['end']]);
    }
}
