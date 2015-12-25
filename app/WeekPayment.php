<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeekPayment extends Model
{
    protected $table = 'payment_week';

    protected $fillable = ['company_id', 'day', 'period', 'type', 'amount'];
}
