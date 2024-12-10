<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attraction;

class TouristAttractionSeeder extends Seeder
{
    public function run()
    {
        $attractions = [
            // Beach & Water Activities
            [
                'name' => 'Cloud 9 Surfing Area',
                'description' => 'Famous surfing spot in Siargao known for its perfect waves and stunning beach views.',
                'location' => 'General Luna, Siargao Island',
                'category' => 'Beach',
                'tags' => json_encode(['beach', 'water_sports', 'surfing']),
                'image_url' => 'https://images.pexels.com/photos/1295138/pexels-photo-1295138.jpeg',
                'rating' => 4.8,
                'views' => 0,
                'latitude' => 9.8167,
                'longitude' => 126.1667,
                'contact_info' => json_encode(['phone' => '+63 123 456 7890', 'email' => 'info@cloud9siargao.com']),
                'opening_hours' => json_encode(['mon-sun' => '6:00 AM - 6:00 PM']),
                'admission_fee' => 50.00,
                'contact_email' => 'info@cloud9siargao.com',
                'map_source' => 'https://maps.google.com/?q=9.8167,126.1667',
                'contact_phone' => '+63 123 456 7890'
            ],
            [
                'name' => 'Sugba Lagoon',
                'description' => 'Beautiful lagoon perfect for swimming, paddleboarding, and diving.',
                'location' => 'Del Carmen, Siargao Island',
                'category' => 'Water Activity',
                'tags' => json_encode(['beach', 'water_sports', 'swimming']),
                'image_url' => 'https://images.pexels.com/photos/1450353/pexels-photo-1450353.jpeg',
                'rating' => 4.7,
                'views' => 0,
                'latitude' => 9.8454,
                'longitude' => 126.0544,
                'contact_info' => json_encode(['phone' => '+63 123 456 7891', 'email' => 'info@sugbalagoon.com']),
                'opening_hours' => json_encode(['mon-sun' => '7:00 AM - 5:00 PM']),
                'admission_fee' => 100.00,
                'contact_email' => 'info@sugbalagoon.com',
                'map_source' => 'https://maps.google.com/?q=9.8454,126.0544',
                'contact_phone' => '+63 123 456 7891'
            ],
            [
                'name' => 'Magpupungko Rock Pools',
                'description' => 'Natural rock pools perfect for swimming during low tide.',
                'location' => 'Pilar, Siargao Island',
                'category' => 'Natural Attraction',
                'tags' => json_encode(['beach', 'water_sports', 'swimming', 'nature']),
                'image_url' => 'https://images.pexels.com/photos/1698485/pexels-photo-1698485.jpeg',
                'rating' => 4.6,
                'views' => 0,
                'latitude' => 9.7833,
                'longitude' => 126.1667,
                'contact_info' => json_encode(['phone' => '+63 123 456 7892', 'email' => 'info@magpupungko.com']),
                'opening_hours' => json_encode(['mon-sun' => '8:00 AM - 5:00 PM']),
                'admission_fee' => 75.00,
                'contact_email' => 'info@magpupungko.com',
                'map_source' => 'https://maps.google.com/?q=9.7833,126.1667',
                'contact_phone' => '+63 123 456 7892'
            ],
            [
                'name' => 'Naked Island',
                'description' => 'Beautiful sandbar with crystal clear waters, perfect for swimming and sunbathing.',
                'location' => 'General Luna, Siargao Island',
                'category' => 'Island',
                'tags' => json_encode(['beach', 'water_sports', 'island_hopping']),
                'image_url' => 'https://images.pexels.com/photos/1032650/pexels-photo-1032650.jpeg',
                'rating' => 4.5,
                'views' => 0,
                'latitude' => 9.7667,
                'longitude' => 126.1500,
                'contact_info' => json_encode(['phone' => '+63 123 456 7893', 'email' => 'info@nakedisland.com']),
                'opening_hours' => json_encode(['mon-sun' => '6:00 AM - 5:00 PM']),
                'admission_fee' => 80.00,
                'contact_email' => 'info@nakedisland.com',
                'map_source' => 'https://maps.google.com/?q=9.7667,126.1500',
                'contact_phone' => '+63 123 456 7893'
            ],
            [
                'name' => 'Guyam Island',
                'description' => 'Small tropical island with white sand beaches and palm trees.',
                'location' => 'General Luna, Siargao Island',
                'category' => 'Island',
                'tags' => json_encode(['beach', 'water_sports', 'island_hopping']),
                'image_url' => 'https://images.pexels.com/photos/1021073/pexels-photo-1021073.jpeg',
                'rating' => 4.6,
                'views' => 0,
                'latitude' => 9.7833,
                'longitude' => 126.1667,
                'contact_info' => json_encode(['phone' => '+63 123 456 7894', 'email' => 'info@guyamisland.com']),
                'opening_hours' => json_encode(['mon-sun' => '8:00 AM - 5:00 PM']),
                'admission_fee' => 60.00,
                'contact_email' => 'info@guyamisland.com',
                'map_source' => 'https://maps.google.com/?q=9.7833,126.1667',
                'contact_phone' => '+63 123 456 7894'
            ],

            // Nature & Adventure
            [
                'name' => 'Mount Hibok-Hibok',
                'description' => 'Active volcano offering challenging hiking trails and panoramic views.',
                'location' => 'Camiguin Island',
                'category' => 'Mountain',
                'tags' => json_encode(['hiking', 'nature', 'adventure', 'mountain_climbing']),
                'image_url' => 'https://images.pexels.com/photos/1658967/pexels-photo-1658967.jpeg',
                'rating' => 4.6,
                'views' => 0,
                'latitude' => 9.2333,
                'longitude' => 124.7333,
                'contact_info' => json_encode(['phone' => '+63 123 456 7895', 'email' => 'info@mounthibokhibok.com']),
                'opening_hours' => json_encode(['mon-sun' => '6:00 AM - 5:00 PM']),
                'admission_fee' => 40.00,
                'contact_email' => 'info@mounthibokhibok.com',
                'map_source' => 'https://maps.google.com/?q=9.2333,124.7333',
                'contact_phone' => '+63 123 456 7895'
            ],
            [
                'name' => 'Sohoton Cave',
                'description' => 'Natural cave system with underground rivers and unique rock formations.',
                'location' => 'Socorro, Surigao del Norte',
                'category' => 'Cave',
                'tags' => json_encode(['nature', 'adventure', 'cave_exploration']),
                'image_url' => 'https://images.pexels.com/photos/237272/pexels-photo-237272.jpeg',
                'rating' => 4.5,
                'views' => 0,
                'latitude' => 9.6167,
                'longitude' => 125.9667,
                'contact_info' => json_encode(['phone' => '+63 123 456 7896', 'email' => 'info@sohotoncave.com']),
                'opening_hours' => json_encode(['mon-sun' => '8:00 AM - 5:00 PM']),
                'admission_fee' => 30.00,
                'contact_email' => 'info@sohotoncave.com',
                'map_source' => 'https://maps.google.com/?q=9.6167,125.9667',
                'contact_phone' => '+63 123 456 7896'
            ],

            // Cultural & Historical
            [
                'name' => 'San Agustin Museum',
                'description' => 'Historical museum showcasing Filipino cultural heritage and religious artifacts.',
                'location' => 'Cagayan de Oro',
                'category' => 'Museum',
                'tags' => json_encode(['culture', 'history', 'museum', 'arts']),
                'image_url' => 'https://images.pexels.com/photos/2034373/pexels-photo-2034373.jpeg',
                'rating' => 4.3,
                'views' => 0,
                'latitude' => 8.4833,
                'longitude' => 124.6333,
                'contact_info' => json_encode(['phone' => '+63 123 456 7897', 'email' => 'info@sanagustinmuseum.com']),
                'opening_hours' => json_encode(['mon-sun' => '9:00 AM - 5:00 PM']),
                'admission_fee' => 20.00,
                'contact_email' => 'info@sanagustinmuseum.com',
                'map_source' => 'https://maps.google.com/?q=8.4833,124.6333',
                'contact_phone' => '+63 123 456 7897'
            ],
            [
                'name' => 'Butuan National Museum',
                'description' => 'Archaeological museum featuring ancient artifacts and historical exhibits.',
                'location' => 'Butuan City',
                'category' => 'Museum',
                'tags' => json_encode(['culture', 'history', 'museum', 'archaeology']),
                'image_url' => 'https://images.pexels.com/photos/1839919/pexels-photo-1839919.jpeg',
                'rating' => 4.4,
                'views' => 0,
                'latitude' => 8.9500,
                'longitude' => 125.5333,
                'contact_info' => json_encode(['phone' => '+63 123 456 7898', 'email' => 'info@butuannationalmuseum.com']),
                'opening_hours' => json_encode(['mon-sun' => '9:00 AM - 5:00 PM']),
                'admission_fee' => 25.00,
                'contact_email' => 'info@butuannationalmuseum.com',
                'map_source' => 'https://maps.google.com/?q=8.9500,125.5333',
                'contact_phone' => '+63 123 456 7898'
            ],

            // Food & Culinary
            [
                'name' => 'Higalaay Food Festival',
                'description' => 'Annual food festival showcasing local Mindanao cuisine and delicacies.',
                'location' => 'Cagayan de Oro',
                'category' => 'Food Festival',
                'tags' => json_encode(['food', 'culinary', 'local_cuisine', 'festivals']),
                'image_url' => 'https://images.pexels.com/photos/2608512/pexels-photo-2608512.jpeg',
                'rating' => 4.7,
                'views' => 0,
                'latitude' => 8.4833,
                'longitude' => 124.6333,
                'contact_info' => json_encode(['phone' => '+63 123 456 7899', 'email' => 'info@higalaayfoodfestival.com']),
                'opening_hours' => json_encode(['mon-sun' => '10:00 AM - 10:00 PM']),
                'admission_fee' => 0.00,
                'contact_email' => 'info@higalaayfoodfestival.com',
                'map_source' => 'https://maps.google.com/?q=8.4833,124.6333',
                'contact_phone' => '+63 123 456 7899'
            ],
            [
                'name' => 'Night Cafe',
                'description' => 'Popular night market featuring local street food and entertainment.',
                'location' => 'Divisoria, Cagayan de Oro',
                'category' => 'Food Market',
                'tags' => json_encode(['food', 'night_market', 'street_food', 'entertainment']),
                'image_url' => 'https://images.pexels.com/photos/2034830/pexels-photo-2034830.jpeg',
                'rating' => 4.5,
                'views' => 0,
                'latitude' => 8.4833,
                'longitude' => 124.6333,
                'contact_info' => json_encode(['phone' => '+63 123 456 7900', 'email' => 'info@nightcafe.com']),
                'opening_hours' => json_encode(['mon-sun' => '6:00 PM - 2:00 AM']),
                'admission_fee' => 0.00,
                'contact_email' => 'info@nightcafe.com',
                'map_source' => 'https://maps.google.com/?q=8.4833,124.6333',
                'contact_phone' => '+63 123 456 7900'
            ],
        ];

        foreach ($attractions as $attraction) {
            Attraction::updateOrCreate(
                ['name' => $attraction['name']],
                $attraction
            );
        }
    }
}
