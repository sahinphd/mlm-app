<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['title','subtitle','start_date','end_date','progress','status','participants'];

    protected $casts = [
        'participants' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
