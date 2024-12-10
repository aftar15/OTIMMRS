<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeeder extends Seeder
{
    public function run()
    {
        $accommodations = [
            [
                'name' => 'Shangri-La Boracay Resort & Spa',
                'description' => 'Luxury beachfront resort with private beach access, multiple pools, and world-class spa facilities.',
                'location' => 'Boracay Island, Malay, Aklan',
                'category' => 'Resort',
                'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3',
                'price_per_night' => 15000.00,
                'capacity' => 4,
                'amenities' => json_encode(['Pool', 'Spa', 'Restaurant', 'Beach Access', 'WiFi', 'Room Service']),
                'contact_info' => json_encode([
                    'phone' => '+63 36 288 4988',
                    'email' => 'reservations@shangri-la-boracay.com',
                    'website' => 'www.shangri-la.com/boracay'
                ]),
                'views' => 1200
            ],
            [
                'name' => 'El Nido Resorts Pangulasian Island',
                'description' => 'Eco-luxury resort offering stunning views of Bacuit Bay and sustainable tourism practices.',
                'location' => 'Pangulasian Island, El Nido, Palawan',
                'category' => 'Resort',
                'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3',
                'price_per_night' => 25000.00,
                'capacity' => 3,
                'amenities' => json_encode(['Private Beach', 'Diving Center', 'Eco Tours', 'Spa', 'Restaurant']),
                'contact_info' => json_encode([
                    'phone' => '+63 2 8813 0000',
                    'email' => 'reservations@elnidoresorts.com',
                    'website' => 'www.elnidoresorts.com'
                ]),
                'views' => 980
            ],
            [
                'name' => 'The Farm at San Benito',
                'description' => 'Holistic medical wellness resort offering health programs and vegan cuisine.',
                'location' => 'Lipa City, Batangas',
                'category' => 'Wellness Resort',
                'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3',
                'price_per_night' => 20000.00,
                'capacity' => 2,
                'amenities' => json_encode(['Spa', 'Yoga Center', 'Vegan Restaurant', 'Meditation Areas', 'Wellness Programs']),
                'contact_info' => json_encode([
                    'phone' => '+63 2 8884 8074',
                    'email' => 'info@thefarm.com.ph',
                    'website' => 'www.thefarm.com.ph'
                ]),
                'views' => 850
            ]
        ];

        foreach ($accommodations as $accommodation) {
            Accommodation::create($accommodation);
        }
    }
}
