<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tourist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TouristSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing tourists
        Tourist::truncate();

        // Predefined tourist data with diverse profiles
        $tourists = [
            [
                'full_name' => 'Emily Johnson',
                'email' => 'emily.johnson@example.com',
                'password' => Hash::make('tourist123'),
                'gender' => 'Female',
                'nationality' => 'United States',
                'address' => '123 Beach Road, Miami, FL',
                'hobbies' => json_encode([
                    ['name' => 'Beach Activities'],
                    ['name' => 'Photography']
                ]),
                'accommodation_name' => 'Sunshine Resort',
                'accommodation_location' => 'Miami Beach',
                'accommodation_days' => 7
            ],
            [
                'full_name' => 'Carlos Rodriguez',
                'email' => 'carlos.rodriguez@example.com',
                'password' => Hash::make('tourist456'),
                'gender' => 'Male',
                'nationality' => 'Mexico',
                'address' => '456 Mountain View, Cancun',
                'hobbies' => json_encode([
                    ['name' => 'Hiking'],
                    ['name' => 'Local Cuisine']
                ]),
                'accommodation_name' => 'Tropical Paradise Hotel',
                'accommodation_location' => 'Cancun',
                'accommodation_days' => 5
            ],
            [
                'full_name' => 'Aisha Patel',
                'email' => 'aisha.patel@example.com',
                'password' => Hash::make('tourist789'),
                'gender' => 'Female',
                'nationality' => 'India',
                'address' => '789 Cultural Street, Mumbai',
                'hobbies' => json_encode([
                    ['name' => 'Cultural Tours'],
                    ['name' => 'Art Museums']
                ]),
                'accommodation_name' => 'Heritage Inn',
                'accommodation_location' => 'Mumbai',
                'accommodation_days' => 10
            ],
            [
                'full_name' => 'Liam O\'Connor',
                'email' => 'liam.oconnor@example.com',
                'password' => Hash::make('tourist101'),
                'gender' => 'Male',
                'nationality' => 'Ireland',
                'address' => '101 Green Valley, Dublin',
                'hobbies' => json_encode([
                    ['name' => 'Pub Crawling'],
                    ['name' => 'Historical Sites']
                ]),
                'accommodation_name' => 'Celtic Comfort Hotel',
                'accommodation_location' => 'Dublin',
                'accommodation_days' => 6
            ],
            [
                'full_name' => 'Sophie Chen',
                'email' => 'sophie.chen@example.com',
                'password' => Hash::make('tourist202'),
                'gender' => 'Female',
                'nationality' => 'China',
                'address' => '202 Silk Road, Beijing',
                'hobbies' => json_encode([
                    ['name' => 'Shopping'],
                    ['name' => 'Street Food']
                ]),
                'accommodation_name' => 'Imperial Palace Hotel',
                'accommodation_location' => 'Beijing',
                'accommodation_days' => 8
            ]
        ];

        // Create tourists with predefined data
        foreach ($tourists as $touristData) {
            Tourist::create($touristData);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
