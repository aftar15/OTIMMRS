<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        $activities = [
            [
                'name' => 'Mount Pulag Summit Hike',
                'description' => 'Experience the sea of clouds at Luzon\'s highest peak',
                'location' => 'Kabayan, Benguet',
                'category' => 'Hiking',
                'tags' => json_encode(['hiking', 'mountain', 'camping', 'nature']),
                'image_url' => 'https://images.unsplash.com/photo-1551632811-561732d1e306',
                'rating' => 4.8,
                'price' => 2500.00,
                'duration' => '2 days',
                'difficulty' => 'Moderate',
                'included_items' => json_encode(['Guide', 'Camping Equipment', 'Meals', 'First Aid Kit']),
                'start_time' => '2024-01-01 05:00:00',
                'end_time' => '2024-01-02 12:00:00',
                'schedule_type' => 'scheduled',
                'recurring_pattern' => json_encode(['days' => ['Saturday', 'Sunday']]),
                'cost' => 2500.00,
                'capacity' => 20,
                'min_participants' => 5,
                'is_active' => true,
                'requires_booking' => true,
                'booking_deadline_hours' => 48,
                'latitude' => 16.5847,
                'longitude' => 120.8853,
                'map_source' => 'Google Maps',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=400',
                'contact_number' => '+63 915 123 4567'
            ],
            [
                'name' => 'Taal Volcano Crater Trek',
                'description' => 'Trek to the crater of the world\'s smallest active volcano',
                'location' => 'Talisay, Batangas',
                'category' => 'Trekking',
                'tags' => json_encode(['volcano', 'trekking', 'adventure', 'nature']),
                'image_url' => 'https://images.unsplash.com/photo-1564661388016-69b0c177bc67',
                'rating' => 4.6,
                'price' => 1500.00,
                'duration' => '4 hours',
                'difficulty' => 'Easy',
                'included_items' => json_encode(['Guide', 'Boat Ride', 'Safety Gear', 'Water']),
                'start_time' => '2024-01-01 06:00:00',
                'end_time' => '2024-01-01 10:00:00',
                'schedule_type' => 'daily',
                'recurring_pattern' => json_encode(['frequency' => 'daily']),
                'cost' => 1500.00,
                'capacity' => 15,
                'min_participants' => 2,
                'is_active' => true,
                'requires_booking' => true,
                'booking_deadline_hours' => 24,
                'latitude' => 14.0023,
                'longitude' => 120.9934,
                'map_source' => 'Google Maps',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1564661388016-69b0c177bc67?w=400',
                'contact_number' => '+63 917 234 5678'
            ],
            [
                'name' => 'Puerto Galera Scuba Diving',
                'description' => 'Discover the vibrant marine life of Puerto Galera',
                'location' => 'Puerto Galera, Oriental Mindoro',
                'category' => 'Water Sports',
                'tags' => json_encode(['diving', 'marine life', 'water sports', 'adventure']),
                'image_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5',
                'rating' => 4.9,
                'price' => 3500.00,
                'duration' => '6 hours',
                'difficulty' => 'Intermediate',
                'included_items' => json_encode(['Equipment', 'Instructor', 'Lunch', 'Photos']),
                'start_time' => '2024-01-01 08:00:00',
                'end_time' => '2024-01-01 14:00:00',
                'schedule_type' => 'daily',
                'recurring_pattern' => json_encode(['frequency' => 'daily']),
                'cost' => 3500.00,
                'capacity' => 8,
                'min_participants' => 2,
                'is_active' => true,
                'requires_booking' => true,
                'booking_deadline_hours' => 24,
                'latitude' => 13.5124,
                'longitude' => 120.9724,
                'map_source' => 'Google Maps',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?w=400',
                'contact_number' => '+63 919 345 6789'
            ]
        ];

        foreach ($activities as $activity) {
            Activity::create(array_merge($activity, [
                'id' => Str::uuid()
            ]));
        }
    }
}
