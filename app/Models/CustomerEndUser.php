<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerEndUser extends Model
{
    use HasFactory;

    protected $table = 'customer_end_users';

    protected $fillable = [
        'customer_id',
        'name',
        'address',
        'contact_person',
        'telephone',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
