<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerFile extends Model
{
    use HasFactory;

    protected $table = 'customer_files';

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
