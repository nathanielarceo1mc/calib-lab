<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_no',
        'customer_name',
        'contact_person',
        'contact_email',
        'contact_phone',
        'scope',
        'amount',
        'status',
        'po_number',
        'po_date',
        'created_by',
        'approved_at',
    ];

    protected $casts = [
        'po_date'     => 'date',
        'approved_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
}
