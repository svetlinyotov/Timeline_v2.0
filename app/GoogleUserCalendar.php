<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoogleUserCalendar extends Model
{
    protected $table = 'users_linked_google_calendars';

    protected $fillable = ['id', 'user_id', 'calendar_id'];

    public function user()
    {
        return $this->belongsTo('App\GoogleUser');
    }
}
