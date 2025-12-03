<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $table = 'work_orders';

    protected $fillable = [
        'customer_id',
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
        'quotation_number',
        'end_user',
        'receiver_user_id',
        'status',
        'for_approval',
        'notes',
    ];

    protected $casts = [
        'order_date'       => 'date',
        'discount_percent' => 'float',
        'for_approval'     => 'boolean',
    ];

    public function units()
    {
        return $this->hasMany(UnitUnderTest::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
