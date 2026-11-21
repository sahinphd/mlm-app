<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmiSchedule extends Model
{
    protected $fillable = [
        'user_id','order_id','total_amount','installment_amount','interval_days','due_date','status','commission_distributed'
    ];

    protected $casts = [
        'commission_distributed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
