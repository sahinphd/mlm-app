<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Package extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'bv', 'image', 'status'
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'package_product')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
