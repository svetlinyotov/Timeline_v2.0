<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Availability extends Model
{
    protected $table = 'availability';

    protected $fillable = ['id', 'user_id', 'start_time', 'end_time', 'all_day'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function events($user_id, $start, $end)
    {
        return DB::select("
          SELECT
          a.id as id,
          a.start_time as start,
          a.end_time as end,
          a.all_day as allDay,
          users.email as user
          FROM availability a
          LEFT JOIN users ON users.id = a.user_id
          WHERE
            a.user_id = ?
            AND (start_time BETWEEN ? and ? OR end_time BETWEEN ? and ?)
          ", [$user_id, $start, $end, $start, $end]);
    }
}
