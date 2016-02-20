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

    public static function users($company_id, $start, $end)
    {
        if($company_id == null) return null;

        $user = User::whereHas('company', function($q) use ($company_id){$q->where('companies.id', $company_id);})->with('info')->get();

        $arr = [];
        foreach ($user as $value) {
            $rosters = Roster::whereHas('users', function($q) use ($value){$q->where('users.id', $value->id);})->whereBetween('start_time', [$start, $end])->get();
            $sum = 0;
            foreach ($rosters as $roster) {
                $sum += Roster::payment($roster->id, $company_id);
            }
            array_push($arr, ['names' => $value->info->names, 'mobile' => $value->info->mobile, 'email' => $value->email, 'salary' => $sum, 'id' => $value->id]);
        }

        return $arr;
    }

    public static function shifts($user_id, $start, $end)
    {
        $arr = [];
        $rosters = Roster::where('user_id', $user_id)->whereBetween('start_time', [$start, $end])->get();
        foreach ($rosters as $roster) {
            $amount = Roster::payment($roster->id);
            array_push($arr, ['start' => $roster->start_time, 'end' => $roster->end_time, 'real_start' => $roster->real_start_time, 'real_end' => $roster->real_end_time,'amount' => $amount, 'id' => $roster->id, 'address' => $roster->address]);
        }

        return $arr;
    }
}
