<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PersonalInfo extends Model
{
    protected $table = 'usersPersonalInfo';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
