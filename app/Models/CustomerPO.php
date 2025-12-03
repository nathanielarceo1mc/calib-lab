<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerPO extends Model
{
    use HasFactory;

    protected $table = 'customer_pos';

    protected $fillable = [
        'customer_id',
        'label',
        'file_path',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
