<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    protected $table = 'usersPersonalInfo';

    protected $fillable = ['names', 'address', 'mobile', 'gender', 'birth_date', 'home_phone', 'work_phone', 'fax', 'other', 'CV', 'photo', 'last_login'];

    protected $hidden = ['user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
