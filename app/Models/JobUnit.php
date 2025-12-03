<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobUnit extends Model
{
    use HasFactory;

    // Table name is unit_under_test (not job_units)
    protected $table = 'unit_under_test';

    protected $fillable = [
        'job_item_id',
        'reference_no',
        'status',
        'name',
        'manufacturer',
        'model',
        'serial_number',
        'range',
        'remarks',
        'assigned_to',
    ];

    public function item()
    {
        return $this->belongsTo(JobItem::class, 'job_item_id');
    }
}
