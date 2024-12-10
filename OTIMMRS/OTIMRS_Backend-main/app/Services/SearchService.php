<?php

namespace App\Services;

use App\Models\TouristAttraction;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class SearchService
{
    private $cacheTime = 3600; // 1 hour

    public function searchAttractions(array $filters)
    {
        $cacheKey = 'search_attractions_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($filters) {
            $query = TouristAttraction::query()
                ->select('tourist_attractions.*')
                ->selectRaw('COALESCE(AVG(ratings.rating), 0) as avg_rating')
                ->leftJoin('ratings', function ($join) {
                    $join->on('tourist_attractions.id', '=', 'ratings.rateable_id')
                         ->where('ratings.rateable_type', TouristAttraction::class);
                })
                ->groupBy('tourist_attractions.id');

            $this->applyAttractionFilters($query, $filters);

            return $query->paginate($filters['per_page'] ?? 10);
        });
    }

    public function searchActivities(array $filters)
    {
        $cacheKey = 'search_activities_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($filters) {
            $query = Activity::query()
                ->select('activities.*')
                ->selectRaw('COALESCE(AVG(ratings.rating), 0) as avg_rating')
                ->leftJoin('ratings', function ($join) {
                    $join->on('activities.id', '=', 'ratings.rateable_id')
                         ->where('ratings.rateable_type', Activity::class);
                })
                ->where('is_active', true)
                ->groupBy('activities.id');

            $this->applyActivityFilters($query, $filters);

            return $query->paginate($filters['per_page'] ?? 10);
        });
    }

    private function applyAttractionFilters(Builder $query, array $filters)
    {
        // Text search across multiple fields
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%")
                  ->orWhere('tags', 'like', "%{$searchTerm}%");
            });
        }

        // Category/Type filtering
        if (!empty($filters['categories'])) {
            $query->whereJsonContains('tags', $filters['categories']);
        }

        // Location-based filtering
        if (!empty($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        // Price range filtering
        if (isset($filters['min_price'])) {
            $query->where('admission_fee', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('admission_fee', '<=', $filters['max_price']);
        }

        // Rating filter
        if (isset($filters['min_rating'])) {
            $query->having('avg_rating', '>=', $filters['min_rating']);
        }

        // Distance-based search if coordinates provided
        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            $lat = $filters['latitude'];
            $lng = $filters['longitude'];
            $radius = $filters['radius'] ?? 50; // Default 50km radius

            $query->selectRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?)) + sin(radians(?))
                * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        }

        // Sorting
        $sortField = $filters['sort_by'] ?? 'avg_rating';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        if ($sortField === 'distance' && (!isset($filters['latitude']) || !isset($filters['longitude']))) {
            $sortField = 'avg_rating';
        }

        $query->orderBy($sortField, $sortDirection);
    }

    private function applyActivityFilters(Builder $query, array $filters)
    {
        // Text search across multiple fields
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        // Date range filtering
        if (!empty($filters['start_date'])) {
            $query->where('start_time', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('end_time', '<=', $filters['end_date']);
        }

        // Price range filtering
        if (isset($filters['min_price'])) {
            $query->where('cost', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('cost', '<=', $filters['max_price']);
        }

        // Capacity filtering
        if (isset($filters['min_capacity'])) {
            $query->where('capacity', '>=', $filters['min_capacity']);
        }

        // Booking requirement filter
        if (isset($filters['requires_booking'])) {
            $query->where('requires_booking', $filters['requires_booking']);
        }

        // Schedule type filter
        if (!empty($filters['schedule_type'])) {
            $query->where('schedule_type', $filters['schedule_type']);
        }

        // Rating filter
        if (isset($filters['min_rating'])) {
            $query->having('avg_rating', '>=', $filters['min_rating']);
        }

        // Distance-based search if coordinates provided
        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            $lat = $filters['latitude'];
            $lng = $filters['longitude'];
            $radius = $filters['radius'] ?? 50; // Default 50km radius

            $query->selectRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(latitude))
                * cos(radians(longitude) - radians(?)) + sin(radians(?))
                * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        }

        // Sorting
        $sortField = $filters['sort_by'] ?? 'start_time';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        
        if ($sortField === 'distance' && (!isset($filters['latitude']) || !isset($filters['longitude']))) {
            $sortField = 'start_time';
        }

        $query->orderBy($sortField, $sortDirection);
    }
}
