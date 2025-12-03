<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'description',
        'manufacturer',
        'model',
        'serial_number',
        'owner',
        'range',
        'last_cal_date',
        'next_cal_date',
        'status',
    ];
}
