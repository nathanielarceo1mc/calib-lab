<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // All columns you let the form write
    protected $fillable = [
        'code',
        'business_name',
        'nature_of_business',
        'address',
        'region',                     // <- IMPORTANT
        'province',                   // <- IMPORTANT
        'company_email',
        'company_phone',
        'tin',
        'payment_terms',
        'vat_type',
        'certificate_expiration_days',
        'company_type',
        'primary_contact',
        'created_by',
    ];

    // ===== RELATIONSHIPS =====

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contactPersons()
    {
        return $this->hasMany(CustomerContactPerson::class);
    }

    public function endUsers()
    {
        return $this->hasMany(CustomerEndUser::class);
    }

    public function pos()
    {
        return $this->hasMany(CustomerPO::class);
    }

    public function files()
    {
        return $this->hasMany(CustomerFile::class);
    }
}
