<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'credit_account_id',
        'type',
        'amount',
        'source',
        'reference_id',
        'description',
    ];

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(CreditAccount::class);
    }
}
