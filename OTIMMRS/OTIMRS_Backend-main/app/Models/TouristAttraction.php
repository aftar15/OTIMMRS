<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TouristAttraction extends Model
{
    use HasFactory;

    protected $table = 'tourist_attractions';

    protected $fillable = [
        'name',
        'description',
        'location',
        'category',
        'opening_hours',
        'admission_fee',
        'image_url',
        'rating',
        'latitude',
        'longitude',
        'map_source',
        'contact_phone',
        'contact_email',
        'website',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
        'rating' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = ['rating_stats', 'distance'];

    protected $hidden = ['created_at', 'updated_at'];

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function getRatingStatsAttribute()
    {
        $ratings = $this->ratings;
        return [
            'average' => round($ratings->avg('rating'), 1),
            'count' => $ratings->count(),
            'distribution' => [
                5 => $ratings->where('rating', 5)->count(),
                4 => $ratings->where('rating', 4)->count(),
                3 => $ratings->where('rating', 3)->count(),
                2 => $ratings->where('rating', 2)->count(),
                1 => $ratings->where('rating', 1)->count(),
            ]
        ];
    }

    public function getDistanceAttribute()
    {
        if (request()->has(['latitude', 'longitude'])) {
            $lat = request()->latitude;
            $lng = request()->longitude;
            
            return $this->calculateDistance($lat, $lng);
        }
        return null;
    }

    private function calculateDistance($lat, $lng)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        // Haversine formula
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 2);
    }

    public function scopeNearby($query, $lat, $lng, $radius = 10)
    {
        return $query->whereRaw("
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + 
            sin(radians(?)) * sin(radians(latitude)))) <= ?
        ", [$lat, $lng, $lat, $radius]);
    }

    public static function getPopular($operator, $page, $itemsPerPage)
    {
        return self::withAvg('ratings', 'rating')
            ->orderBy('ratings_avg_rating', 'DESC')
            ->where('category', $operator, 'Hotel')
            ->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public static function getRecommended($categories, $operator, $page, $itemsPerPage)
    {
        return self::withAvg('ratings', 'rating')
            ->orderBy('ratings_avg_rating', 'DESC')
            ->where('category', $operator, 'Hotel')
            ->where(function ($query) use ($categories) {
                foreach ($categories as $category) {
                    $query->orWhere('name', 'LIKE', "%{$category}%")
                          ->orWhere('description', 'LIKE', "%{$category}%")
                          ->orWhere('category', 'LIKE', "%{$category}%")
                          ->orWhereJsonContains('tags', $category);
                }
            })
            ->paginate($itemsPerPage, ['*'], 'page', $page);
    }
}

function deg2rad($deg) {
    return $deg * pi() / 180;
}
