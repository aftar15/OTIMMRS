<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\TouristAttractionSeeder;
use Database\Seeders\AccommodationSeeder;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CommentSeeder;
use Database\Seeders\TouristSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TouristSeeder::class,
            TouristAttractionSeeder::class,
            AccommodationSeeder::class,
            ActivitySeeder::class,
            AdminSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
