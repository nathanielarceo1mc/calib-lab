<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_no',
        'date',
        'customer_id',
        'customer_name',
        'address',
        'telephone',
        'contact_person',
        'contact_email',
        'contact_phone',
        'tin',
        'payment_terms',
        'vat_type',
        'reference',
        'discount_percent',
        'validity_days',
        'quotation_request_no',
        'scope',
        'amount',
        'po_amount',
        'prepared_by',
        'status',
        'po_number',
        'po_date',
        'created_by',
        'approved_by',
        'approved_at',
        'proceed_to_receiving',
    ];

    protected $casts = [
        'date'        => 'date',
        'po_date'     => 'date',
        'approved_at' => 'datetime',
        'proceed_to_receiving' => 'boolean',
        'discount_percent' => 'float',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function units()
    {
        return $this->hasMany(QuotationUnit::class);
    }

    public function charges()
    {
        return $this->hasMany(QuotationCharge::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
