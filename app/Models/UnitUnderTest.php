<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitUnderTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'accreditation',
        'service_type',
        'capability',
        'description',
        'type',
        'receiving_method',
        'qty',
        'unit',
        'lead_time',
        'rush_fee',
        'discount',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
