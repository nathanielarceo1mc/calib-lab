<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Default Laravel fields
        'name',
        'email',
        'password',

        // HR / Personal Info
        'first_name',
        'middle_name',
        'middle_initial',
        'last_name',

        // Employment / Details
        'employee_id',
        'position',
        'birth_date',
        'gender',
        'marital_status',

        // Contact Info
        'home_address',
        'home_phone',
        'mobile',
        'tin',

        // Role-based fields (string type for now)
        'roles',
        'groups',
        'service_types',

        // Status
        'is_active',
    ];

    /**
     * Hidden attributes (never exposed in JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts for attribute transformation.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date'        => 'date',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Helper: Proper display name.
     */
    public function getDisplayNameAttribute(): string
    {
        // Prefer full HR name if available
        if ($this->first_name || $this->last_name) {
            return trim("{$this->first_name} {$this->last_name}");
        }

        // Fall back to default "name" field
        return $this->name;
    }
}
