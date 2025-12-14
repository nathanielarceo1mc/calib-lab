<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'nathaniel@1ntegratedmetrology.com'],
            [
                'first_name'        => 'Nathaniel',
                'last_name'         => 'Admin',
                'name'              => 'Nathaniel Admin',
                'password'          => Hash::make('change-me-now'),
                'roles'             => 'System Admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
