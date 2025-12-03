<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'accreditation',
        'service_type',
        'capability',
        'description',
        'type',
        'receiving_method',
        'qty',
        'unit',
        'lead_time_days',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function units()
    {
        return $this->hasMany(JobUnit::class, 'job_item_id');
    }
}
