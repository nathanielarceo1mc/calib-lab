<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    // Use our own table, not Laravel's queue "jobs" table
    protected $table = 'work_orders';

    protected $fillable = [
        'job_number',
        'order_number',
        'order_date',
        'customer_name',
        'address',
        'telephone',
        'email',
        'contact_person',
        'designation',
        'po_number',
        'po_file_path',
        'discount_percent',
		'quotation_id',
        'quotation_number',
        'end_user',
        'receiver_user_id',
        'status',
        'notes',
    ];
public function quotation()
{
    return $this->belongsTo(Quotation::class);
}

    public function items()
    {
        return $this->hasMany(JobItem::class);
    }
}
