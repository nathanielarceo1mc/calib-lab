<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerContactPerson extends Model
{
    use HasFactory;

    protected $table = 'customer_contact_persons';

    protected $fillable = [
        'customer_id',
        'salutation',
        'first_name',
        'last_name',
        'position',
        'email',
        'mobile',
        'telephone',
        'is_accounting',
        'is_active',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
