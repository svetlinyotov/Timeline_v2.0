<?php

namespace App;

use App\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Roster extends Model
{
    protected $table = 'rosters';
    
    protected $fillable = ['is_supervisor', 'start_time', 'end_time', 'real_start_time', 'real_end_time', 'other', 'address', 'status', 'added_by'];

    public function users()
    {
        return $this->belongsToMany('App\User');
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
          ru.user_id as resourceId,
          rosters.start_time as start,
          rosters.end_time as end,
          rosters.name as title,
          rosters.other as description,
          rosters.address as address,
          rosters.coordinates as coordinates,
          rosters.is_supervisor as supervisor,
          ru.status as status,
          users.email as user,
          CASE
              WHEN ru.status = 'pending' OR ru.status = '' THEN 'color-gray'
              WHEN ru.status = 'accepted' THEN 'color-green'
              WHEN ru.status = 'declined' THEN 'color-red'
              WHEN ru.status = 'canceled' THEN 'color-white'
          END as className
          FROM rosters
          left JOIN roster_user ru ON ru.roster_id = rosters.id
          LEFT JOIN users ON users.id = ru.user_id
          LEFT JOIN company_user cu ON cu.user_id = users.id
          WHERE
            cu.company_id = ?
            AND (start_time BETWEEN ? and ? OR end_time BETWEEN ? and ?)
          ", [$company_id, $data['start'], $data['end'], $data['start'], $data['end']]);
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
                SELECT rosters.id
                FROM rosters
                LEFT JOIN roster_user ru ON ru.roster_id = rosters.id
                WHERE ru.user_id = ? AND
                      ((start_time <= ? AND start_time <= ? AND end_time <= ? AND end_time <= ?) OR
                      (start_time >= ? AND start_time >= ? AND end_time >= ? AND end_time >= ?))
            ) $where
            ",
            $event_id==null?[$user_id, $start, $end, $start, $end, $start, $end, $start, $end, $user_id]:[$user_id, $start, $end, $start, $end, $start, $end, $start, $end, $user_id, $event_id]);
        return count($query) != 0;
    }

    public static function payment($id, $company_id) : float
    {
        $company_shift_start = Company::where('id', $company_id)->select('shift_day_start as day', 'shift_night_start as night')->first();

        $roster = Roster::where('id',$id)->select('real_start_time as start', 'real_end_time as end', 'is_supervisor')->first();

        if($roster->start != null && $roster->end != null) {
            $start_str = strtotime($roster->start);
            $end_str = strtotime($roster->end);
            $arr_times = [];


            for ($i = $start_str; $i <= $end_str; $i += 300) {
                $id = date("N", $i) - 1;
                if (Common::isTimeBetween(date("H:i:s", $i), $company_shift_start->day, $company_shift_start->night)) $id .= "_day"; else $id .= "_night";
                if ($roster->is_supervisor == 1) $id .= "_supervisor"; else $id .= "_worker";
                !isset($arr_times[$id]) ? $arr_times[$id] = 1 : $arr_times[$id] += 1;
            }

            $payment = 0;

            foreach ($arr_times as $key => $time_count) {
                list($day, $period, $type) = explode('_', $key);
                $amount = Payment::week($day, $period, $type, $company_id);
                $payment += $amount * ((5 / 60) * $time_count);
            }

            return number_format((float)$payment, 2, '.', '')??0;
        }
        return 0;
    }
}
