<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Tourist;
use App\Models\Attraction;
use Illuminate\Support\Str;

class CommentSeeder extends Seeder
{
    public function run()
    {
        // Get all tourists and attractions
        $tourists = Tourist::all();
        $attractions = Attraction::all();

        if ($tourists->isEmpty() || $attractions->isEmpty()) {
            return;
        }

        foreach ($attractions as $attraction) {
            // Add 2-5 random comments for each attraction
            $numComments = rand(2, 5);
            
            for ($i = 0; $i < $numComments; $i++) {
                Comment::create([
                    'id' => Str::uuid(),
                    'tourist_id' => $tourists->random()->id,
                    'commentable_id' => $attraction->id,
                    'commentable_type' => 'App\Models\Attraction', // Fully qualified class name
                    'comment' => 'Great place to visit! ' . fake()->sentence(),
                    'transportation' => fake()->randomElement(['Car', 'Bus', 'Motorcycle', 'Walking']),
                    'transportation_fee' => fake()->randomFloat(2, 50, 500),
                    'services' => fake()->optional()->sentence(),
                    'road_problems' => fake()->optional()->sentence(),
                    'price_increase' => fake()->optional()->sentence(),
                    'others' => fake()->optional()->sentence()
                ]);
            }
        }
    }
}
