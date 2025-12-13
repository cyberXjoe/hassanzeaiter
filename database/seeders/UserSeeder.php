<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'guest@olx.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('secret'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@olx.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('secret'),
            ]
        );
    }
}
