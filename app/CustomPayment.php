<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomPayment extends Model
{
    protected $table = 'payment_custom';

    protected $fillable = ['company_id', 'time_start', 'time_end', 'amount'];

    public static function deletePayment($company_id, $payment_id) : bool
    {
        DB::delete('DELETE FROM payment_custom WHERE company_id in (SELECT company_id FROM users WHERE id = ?) AND id = ? AND company_id = ?', [Auth::user()->id, $payment_id, $company_id]);
        return true;
    }
}
