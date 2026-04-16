<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmiSchedule extends Model
{
    protected $fillable = [
        'user_id','order_id','total_amount','installment_amount','interval_days','due_date','status'
    ];
}
