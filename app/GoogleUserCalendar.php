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

    public static function freeBusy($user_id, $start_date, $end_date)
    {
        $tokens = GoogleUser::select('id', 'googleAccessToken')->with(['calendars' => function($q) {$q->select('user_id', 'calendar_id');}])->where('user_id', '=', $user_id)->get();
        $free_busy_requests = [];

        foreach ($tokens as $token) {
            $calendars = $token->calendars->pluck('calendar_id');
            $free_busy_requests = array_merge($free_busy_requests, self::freeBusyRequest($token->id, $token->googleAccessToken, $calendars, $start_date, $end_date));
        }

        return $free_busy_requests;
    }

    public static function freeBusyRequest($google_id, $token, $calendars, $start_date, $end_date)
    {
        $calendar_list = "";
        $i = 0;
        foreach ($calendars as $calendar) {
            $calendar_list .= '{"id":"'.$calendar.'"}';
            $i++;
            if($i != count($calendars)) $calendar_list .= ",";
        }

        $response = ExternalRequest::POST("https://www.googleapis.com/calendar/v3/freeBusy", [], $google_id,$token,'
        {
         "timeMin": "'.$start_date.'T00:00:00Z",
         "timeMax": "'.$end_date.'T23:59:59Z",
         "items": [
           '.$calendar_list.'
         ]
        }
        ');

        if(isset($response->calendars)){
            return (array)$response->calendars;
        }

        return [];
    }
}
