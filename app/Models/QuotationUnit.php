<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'accreditation',
        'service_type',
        'scope',
        'description',
        'range',
        'cmc',
        'method',
        'environment',
        'type',
        'qty',
        'uom',
        'price',
        'discount',
        'rush_fee',
        'total_price',
        'lead_time',
        'notes',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
