<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BvCommission extends Model
{
    protected $fillable = [
        'user_id',
        'from_user_id',
        'order_id',
        'level',
        'amount',
        'status',
        'withdrawable_at',
        'note'
    ];

    protected $casts = [
        'withdrawable_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
