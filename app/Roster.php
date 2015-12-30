<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Roster extends Model
{
    protected $table = 'rosters';
    
    protected $fillable = ['user_id', 'is_supervisor', 'start_time', 'end_time', 'real_start_time', 'real_end_time', 'other', 'address', 'status', 'added_by'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function added_by_user()
    {
        return $this->belongsTo('App\User', 'id', 'added_by');
    }

    public static function eventsJSON($company_id, $data)
    {
        return DB::select("
          SELECT
          rosters.id as id,
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
          END as className
          FROM rosters LEFT JOIN users ON users.id = rosters.user_id WHERE users.company_id = ? AND start_time BETWEEN ? and ? OR end_time BETWEEN ? and ?", [$company_id, $data['start'], $data['end'], $data['start'], $data['end']]);
    }

    public static function overlap($user_id, $start, $end, $event_id=null)
    {
        $where = "";
        if($event_id != null){
            $where = DB::raw("AND id != ?");
        }
        $query = DB::select("
            SELECT * FROM rosters
            WHERE id NOT IN (
                SELECT id
                FROM rosters
                WHERE user_id = ? AND
                      ((start_time <= ? AND start_time <= ? AND end_time <= ? AND end_time <= ?) OR
                      (start_time >= ? AND start_time >= ? AND end_time >= ? AND end_time >= ?))
            ) AND user_id = ? $where
            ",
            $event_id==null?[$user_id, $start, $end, $start, $end, $start, $end, $start, $end, $user_id]:[$user_id, $start, $end, $start, $end, $start, $end, $start, $end, $user_id, $event_id]);
        return count($query) != 0;
    }
}
