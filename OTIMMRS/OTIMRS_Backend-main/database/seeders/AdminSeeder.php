<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin'),
                'name' => 'System Administrator',
                'email' => 'admin@otimmrs.com',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}
