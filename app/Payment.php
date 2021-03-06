<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    public static function week($day, $period, $type, $company_id)
    {
        return DB::select("SELECT amount FROM payment_week WHERE day = ? AND period = ? AND `type` = ? AND company_id = ?", [$day, $period, $type, $company_id])[0]->amount;
    }

    public static function custom($time)
    {
        return DB::select("SELECT SUM(amount) as amount FROM payment_custom WHERE ? BETWEEN time_start AND time_end", [$time])[0]->amount;
    }

    public static function singleUser($user, $start, $end, $company_id = null)
    {
        if($company_id != null) {
            $rosters = Roster::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id)->where('roster_user.status', '=', 'accepted');
            })->whereBetween('start_time', [$start, $end])->where('company_id', $company_id)->get();
        }else {
            $rosters = Roster::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id)->where('roster_user.status', '=', 'accepted');
            })->whereBetween('start_time', [$start, $end])->get();
        }
        $sum = 0;
        $time = 0;
        foreach ($rosters as $roster) {
            $payment = Roster::payment($roster->id, $user->id, $roster->company_id);
            $sum += $payment['payment'];
            $time += $payment['time'];
        }
        
        return ['names' => $user->info->names, 'mobile' => $user->info->mobile, 'email' => $user->email, 'salary' => $sum, 'time' => $time, 'id' => $user->id];
    }

    public static function users($company_id, $start, $end)
    {
        if($company_id == null) return null;
        $arr = [];
        $users = User::whereHas('company', function($q) use ($company_id){$q->where('companies.id', $company_id);})->with('info')->get();


        foreach ($users as $user) {
            array_push($arr, self::singleUser($user, $start, $end, $company_id));
        }

        return $arr;
    }

    public static function shifts($user_id, $company_id, $start, $end)
    {
        $arr = [];
        $rosters = Roster::whereHas('users',
                        function($q) use ($user_id) {
                            $q->where('users.id', $user_id)->where('roster_user.status', '=', 'accepted');
                        }
                    )
                    ->whereBetween('start_time', [$start, $end])
                    ->where('company_id', $company_id)
                    ->get();

        foreach ($rosters as $roster) {
            $roster_pivot = $roster->users()->where('users.id', $user_id)->first()->pivot;
            //return var_dump(Roster::payment($roster->id, $user_id, $company_id));
            $amount = Roster::payment($roster->id, $user_id, $company_id)['payment'];
            array_push($arr, ['start' => $roster->start_time, 'end' => $roster->end_time, 'real_start' => $roster_pivot->real_start_time, 'real_end' => $roster_pivot->real_end_time,'amount' => $amount, 'id' => $roster->id, 'address' => $roster->address]);
        }

        return $arr;
    }
}
