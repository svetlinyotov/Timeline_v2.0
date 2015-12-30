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
    protected $fillable = ['names', 'email', 'password'];

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
        return $this->belongsTo('App\Company');
    }

    public function rosters()
    {
        return $this->hasMany('App\Roster');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification');
    }

    public static function events($user_id, $data)
    {
        return User::find($user_id)->join('rosters', 'rosters.user_id', '=', 'users.id')->select(DB::raw("rosters.id as id,
          rosters.user_id as resourceId,
          rosters.start_time as start,
          rosters.end_time as end,
          rosters.name as title,
          rosters.other as description,
          rosters.address as address,
          rosters.coordinates as coordinates,
          rosters.is_supervisor as supervisor,
          rosters.status as status,
          users.email as user,
          CASE
              WHEN rosters.status = 'pending' OR rosters.status = '' THEN 'color-gray'
              WHEN rosters.status = 'accepted' THEN 'color-green'
              WHEN rosters.status = 'declined' THEN 'color-red'
              WHEN rosters.status = 'canceled' THEN 'color-white'
          END as className"))->get();
    }
}
