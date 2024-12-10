<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\TouristAttraction;
use App\Models\UserPreference;
use App\Models\Rating;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class RecommendationService
{
    private $cacheTime = 3600; // 1 hour

    public function getRecommendedActivities($userId, $limit = 10)
    {
        return Cache::remember("user.{$userId}.recommended_activities", $this->cacheTime, function () use ($userId, $limit) {
            $preferences = UserPreference::where('user_id', $userId)->first();
            if (!$preferences) {
                return $this->getPopularActivities($limit);
            }

            // Get user's interests and history
            $interests = $preferences->interests ?? [];
            $activityHistory = collect($preferences->activity_history ?? []);
            $recentActivities = $activityHistory->pluck('activity_id')->take(20);

            // Build recommendation query
            $query = Activity::query()
                ->select('activities.*')
                ->selectRaw('COALESCE(AVG(ratings.rating), 0) as avg_rating')
                ->leftJoin('ratings', function ($join) {
                    $join->on('activities.id', '=', 'ratings.rateable_id')
                         ->where('ratings.rateable_type', Activity::class);
                })
                ->where('activities.is_active', true)
                ->whereNotIn('activities.id', $recentActivities); // Exclude recently viewed/booked

            // Apply interest-based filtering
            if (!empty($interests)) {
                $query->where(function ($q) use ($interests) {
                    foreach ($interests as $interest) {
                        $q->orWhere('name', 'like', "%{$interest}%")
                          ->orWhere('description', 'like', "%{$interest}%");
                    }
                });
            }

            // Apply price range preferences if set
            if ($preferences->preferred_price_range_max) {
                $query->where('cost', '<=', $preferences->preferred_price_range_max);
            }
            if ($preferences->preferred_price_range_min) {
                $query->where('cost', '>=', $preferences->preferred_price_range_min);
            }

            // Apply location preferences if set
            if (!empty($preferences->preferred_locations)) {
                $query->where(function ($q) use ($preferences) {
                    foreach ($preferences->preferred_locations as $location) {
                        $q->orWhere('location', 'like', "%{$location}%");
                    }
                });
            }

            // Calculate recommendation score
            $query->groupBy('activities.id')
                  ->orderByRaw('
                      (COALESCE(AVG(ratings.rating), 0) * 0.4) + 
                      (activities.capacity / (SELECT MAX(capacity) FROM activities) * 0.3) +
                      (CASE WHEN activities.schedule_type = "recurring" THEN 0.2 ELSE 0 END) +
                      (CASE WHEN activities.requires_booking THEN 0.1 ELSE 0 END)
                      DESC
                  ');

            return $query->take($limit)->get();
        });
    }

    public function getRecommendedAttractions($userId, $limit = 10)
    {
        return Cache::remember("user.{$userId}.recommended_attractions", $this->cacheTime, function () use ($userId, $limit) {
            $preferences = UserPreference::where('user_id', $userId)->first();
            if (!$preferences) {
                return $this->getPopularAttractions($limit);
            }

            $query = TouristAttraction::query()
                ->select('tourist_attractions.*')
                ->selectRaw('COALESCE(AVG(ratings.rating), 0) as avg_rating')
                ->leftJoin('ratings', function ($join) {
                    $join->on('tourist_attractions.id', '=', 'ratings.rateable_id')
                         ->where('ratings.rateable_type', TouristAttraction::class);
                });

            // Apply interest-based filtering
            if (!empty($preferences->interests)) {
                $query->where(function ($q) use ($preferences) {
                    foreach ($preferences->interests as $interest) {
                        $q->orWhere('name', 'like', "%{$interest}%")
                          ->orWhere('description', 'like', "%{$interest}%")
                          ->orWhere('tags', 'like', "%{$interest}%");
                    }
                });
            }

            // Calculate recommendation score
            $query->groupBy('tourist_attractions.id')
                  ->orderByRaw('
                      (COALESCE(AVG(ratings.rating), 0) * 0.6) + 
                      (CASE WHEN tourist_attractions.admission_fee <= ? THEN 0.2 ELSE 0 END) +
                      (CASE WHEN tourist_attractions.tags IS NOT NULL THEN 0.2 ELSE 0 END)
                      DESC
                  ', [$preferences->preferred_price_range_max ?? PHP_FLOAT_MAX]);

            return $query->take($limit)->get();
        });
    }

    public function getPopularActivities($limit = 10)
    {
        return Cache::remember("popular_activities", $this->cacheTime, function () use ($limit) {
            return Activity::select('activities.*')
                ->selectRaw('COALESCE(AVG(ratings.rating), 0) as avg_rating')
                ->selectRaw('COUNT(DISTINCT ratings.id) as rating_count')
                ->leftJoin('ratings', function ($join) {
                    $join->on('activities.id', '=', 'ratings.rateable_id')
                         ->where('ratings.rateable_type', Activity::class);
                })
                ->where('is_active', true)
                ->groupBy('activities.id')
                ->orderByDesc('avg_rating')
                ->orderByDesc('rating_count')
                ->take($limit)
                ->get();
        });
    }

    public function getPopularAttractions($limit = 10)
    {
        return Cache::remember("popular_attractions", $this->cacheTime, function () use ($limit) {
            return TouristAttraction::select('tourist_attractions.*')
                ->selectRaw('COALESCE(AVG(ratings.rating), 0) as avg_rating')
                ->selectRaw('COUNT(DISTINCT ratings.id) as rating_count')
                ->leftJoin('ratings', function ($join) {
                    $join->on('tourist_attractions.id', '=', 'ratings.rateable_id')
                         ->where('ratings.rateable_type', TouristAttraction::class);
                })
                ->groupBy('tourist_attractions.id')
                ->orderByDesc('avg_rating')
                ->orderByDesc('rating_count')
                ->take($limit)
                ->get();
        });
    }

    public function updateUserPreferences($userId, array $data)
    {
        $preferences = UserPreference::firstOrNew(['user_id' => $userId]);
        $preferences->fill($data);
        $preferences->save();

        // Clear user's recommendation cache
        Cache::forget("user.{$userId}.recommended_activities");
        Cache::forget("user.{$userId}.recommended_attractions");

        return $preferences;
    }

    public function trackUserAction($userId, $itemId, $itemType, $action)
    {
        $preferences = UserPreference::firstOrNew(['user_id' => $userId]);

        switch ($action) {
            case 'view':
                $preferences->updateClickHistory($itemId, $itemType);
                break;
            case 'book':
            case 'rate':
            case 'comment':
                $preferences->updateActivityHistory($itemId, $action);
                break;
            case 'search':
                $preferences->updateSearchHistory($itemId); // In this case, itemId is the search query
                break;
        }

        // Clear recommendation cache for this user
        Cache::forget("user.{$userId}.recommended_activities");
        Cache::forget("user.{$userId}.recommended_attractions");
    }
}
