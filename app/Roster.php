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
        return $this->belongsToMany('App\User')->withPivot('is_supervisor', 'real_start_time', 'real_end_time', 'status');
    }

    public function added_by_user()
    {
        return $this->belongsTo('App\User', 'id', 'added_by');
    }

    public static function eventsJSON($company_id, $data)
    {
        return DB::select("
          SELECT
              r.id as id,
              ru.user_id as resourceId,
              r.start_time as start,
              r.end_time as end,
              '' as rendering,
              users.events_color as color,
              r.name as title,
              ru.status as status,
              CASE
                  WHEN ru.status = 'pending' OR ru.status = '' THEN 'color-gray'
                  WHEN ru.status = 'accepted' THEN 'color-green'
                  WHEN ru.status = 'declined' THEN 'color-red'
                  WHEN ru.status = 'canceled' THEN 'color-white'
              END as className
          FROM rosters r
              JOIN roster_user ru ON ru.roster_id = r.id
              LEFT JOIN users ON users.id = ru.user_id
          WHERE
              r.company_id = ?
              AND (r.start_time BETWEEN ? and ? OR r.end_time BETWEEN ? and ?)

          UNION

          SELECT
              a.id as id,
              a.user_id as resourceId,
              a.start_time as start,
              a.end_time as end,
              'background' as rendering,
              users.events_color as color,
              '' as title,
              '' as status,
              '' as className
          FROM availability a
          LEFT JOIN users ON users.id = a.user_id
          WHERE a.start_time BETWEEN ? and ? OR a.end_time BETWEEN ? and ?

          ", [$company_id, $data['start'], $data['end'], $data['start'], $data['end'], $data['start'], $data['end'], $data['start'], $data['end']]);
    }

    public static function add(Array $data) : bool
    {
        $pdo = DB::connection()->getPdo();
        $roster_insert = DB::insert("INSERT INTO rosters (company_id, name, start_time, end_time, other, address, coordinates, added_by, updated_at, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$data['company_id'], $data['name'], $data['start_time'], $data['end_time'], $data['other'], $data['address'], $data['coordinates'], $data['added_by'], new \DateTime(), new \DateTime()]);

        if($roster_insert){
            $id = $pdo->lastInsertId();
            $roster_user_insert = DB::insert("INSERT INTO roster_user (user_id, is_supervisor, roster_id) VALUES (?,?,?)", [$data['id'], $data['is_supervisor'], $id]);
            if($roster_user_insert){
                return true;
            }
        }

        return false;
    }

    public static function overlap(int $user_id, $start, $end, $event_id = null)
    {
        $where = "";
        if($event_id != null){
            $where = DB::raw("AND id != :event_id");
        }
        $query = DB::select("
            SELECT * FROM rosters
            WHERE id IN (
                SELECT rosters.id
                FROM rosters
                JOIN roster_user ru ON ru.roster_id = rosters.id
                WHERE ru.user_id = :user_id AND
                      (:end >= start_time AND end_time >= :start)
            ) $where
            ",
            $event_id==null?['user_id' => $user_id, 'start'=>$start, 'end'=>$end]:['user_id' => $user_id, 'start'=>$start, 'end'=>$end, 'event_id' => $event_id]);

        return count($query) != 0;
    }

    public static function payment(int $id, $user_id, string $company_id) : array
    {
        $company_shift_start = Company::where('id', $company_id)->select('shift_day_start as day', 'shift_night_start as night')->first();


        $roster = Roster::find($id)->users()->where('users.id', $user_id)->first();

        if(count($roster) > 0 && $roster->pivot->real_start_time != null && $roster->pivot->real_start_time != null) {
            $start_str = strtotime($roster->pivot->real_start_time);
            $end_str = strtotime($roster->pivot->real_end_time);
            $arr_times = [];

            $payment = 0;
            $time = 0;

            for ($i = $start_str; $i <= $end_str; $i += 300) {
                $current_checking_time = date("Y-m-d H:i:s", $i);
                $custom_payment_amount = Payment::custom($current_checking_time);

                if($custom_payment_amount == 0) {
                    $id = date("N", $i) - 1;
                    if (Common::isTimeBetween(date("H:i:s", $i), $company_shift_start->day, $company_shift_start->night)) $id .= "_day"; else $id .= "_night";
                    if ($roster->pivot->is_supervisor == 1) $id .= "_supervisor"; else $id .= "_worker";
                    !isset($arr_times[$id]) ? $arr_times[$id] = 1 : $arr_times[$id] += 1;
                }else{
                    $payment += $custom_payment_amount * (1 / 12);
                    $time += 1/12;
                }
            }

            foreach ($arr_times as $key => $time_count) {
                list($day, $period, $type) = explode('_', $key);
                $amount = Payment::week($day, $period, $type, $company_id);
                $payment += $amount * ((5 / 60) * $time_count);
                $time += $time_count;
                //return var_dump($amount);
            }

            return ['payment' => number_format((float)$payment, 2, '.', '')??0, 'time' => $time];
        }
        return ['payment' => 0, 'time' => 0];
    }
}
