<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    // Your DB table (we saw queries going to work_orders)
    protected $table = 'work_orders';

    protected $fillable = [
        'order_number',
        'order_date',
        'customer_name',
        'contact_person',
        'status',
        'receiver_name',
        'for_approval',

        // optional fields you may add later:
        'address',
        'telephone',
        'email',
        'notes',
        'po_number',
        'quotation_number',
        'end_user',
        'discount_percent',
    ];

    protected $casts = [
        'order_date'   => 'date',
        'for_approval' => 'boolean',
    ];
}
