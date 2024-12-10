<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\TouristAttraction;
use App\Models\Rating;
use App\Models\Tourist;
use App\Models\ActivityBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportService
{
    private $cacheTime = 3600; // 1 hour

    public function getOverviewStats()
    {
        return Cache::remember('overview_stats', $this->cacheTime, function () {
            $now = Carbon::now();
            $startOfMonth = $now->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();

            return [
                'total_tourists' => Tourist::count(),
                'total_attractions' => TouristAttraction::count(),
                'total_activities' => Activity::count(),
                'total_bookings' => ActivityBooking::count(),
                'monthly_stats' => [
                    'new_tourists' => Tourist::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                    'new_bookings' => ActivityBooking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                    'revenue' => ActivityBooking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                        ->sum('total_amount'),
                ],
                'ratings_overview' => $this->getRatingsOverview(),
            ];
        });
    }

    public function getAttractionStats($timeframe = 'month')
    {
        $cacheKey = "attraction_stats_{$timeframe}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($timeframe) {
            $dateRange = $this->getDateRange($timeframe);
            
            return [
                'most_visited' => $this->getMostVisitedAttractions($dateRange),
                'highest_rated' => $this->getHighestRatedAttractions(),
                'category_distribution' => $this->getAttractionCategoryDistribution(),
                'rating_trends' => $this->getAttractionRatingTrends($dateRange),
            ];
        });
    }

    public function getActivityStats($timeframe = 'month')
    {
        $cacheKey = "activity_stats_{$timeframe}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($timeframe) {
            $dateRange = $this->getDateRange($timeframe);
            
            return [
                'popular_activities' => $this->getPopularActivities($dateRange),
                'booking_trends' => $this->getBookingTrends($dateRange),
                'revenue_analysis' => $this->getRevenueAnalysis($dateRange),
                'capacity_utilization' => $this->getCapacityUtilization($dateRange),
            ];
        });
    }

    public function getTouristStats($timeframe = 'month')
    {
        $cacheKey = "tourist_stats_{$timeframe}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($timeframe) {
            $dateRange = $this->getDateRange($timeframe);
            
            return [
                'registration_trends' => $this->getRegistrationTrends($dateRange),
                'demographic_data' => $this->getDemographicData(),
                'engagement_metrics' => $this->getEngagementMetrics($dateRange),
                'preference_analysis' => $this->getPreferenceAnalysis(),
            ];
        });
    }

    private function getRatingsOverview()
    {
        return Rating::select(
            DB::raw('ROUND(rating) as rating_round'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('rating_round')
        ->orderBy('rating_round')
        ->get()
        ->mapWithKeys(function ($item) {
            return [$item->rating_round => $item->count];
        });
    }

    private function getMostVisitedAttractions($dateRange)
    {
        return TouristAttraction::select(
            'tourist_attractions.*',
            DB::raw('COUNT(DISTINCT ratings.id) as visit_count'),
            DB::raw('AVG(ratings.rating) as avg_rating')
        )
        ->leftJoin('ratings', function ($join) use ($dateRange) {
            $join->on('tourist_attractions.id', '=', 'ratings.rateable_id')
                 ->where('ratings.rateable_type', TouristAttraction::class)
                 ->whereBetween('ratings.created_at', $dateRange);
        })
        ->groupBy('tourist_attractions.id')
        ->orderByDesc('visit_count')
        ->limit(10)
        ->get();
    }

    private function getHighestRatedAttractions()
    {
        return TouristAttraction::select(
            'tourist_attractions.*',
            DB::raw('AVG(ratings.rating) as avg_rating'),
            DB::raw('COUNT(ratings.id) as rating_count')
        )
        ->leftJoin('ratings', function ($join) {
            $join->on('tourist_attractions.id', '=', 'ratings.rateable_id')
                 ->where('ratings.rateable_type', TouristAttraction::class);
        })
        ->groupBy('tourist_attractions.id')
        ->having('rating_count', '>=', 5) // Minimum ratings threshold
        ->orderByDesc('avg_rating')
        ->limit(10)
        ->get();
    }

    private function getAttractionCategoryDistribution()
    {
        return TouristAttraction::select('tags', DB::raw('COUNT(*) as count'))
            ->whereNotNull('tags')
            ->groupBy('tags')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->tags => $item->count];
            });
    }

    private function getPopularActivities($dateRange)
    {
        return Activity::select(
            'activities.*',
            DB::raw('COUNT(activity_bookings.id) as booking_count'),
            DB::raw('AVG(ratings.rating) as avg_rating')
        )
        ->leftJoin('activity_bookings', 'activities.id', '=', 'activity_bookings.activity_id')
        ->leftJoin('ratings', function ($join) {
            $join->on('activities.id', '=', 'ratings.rateable_id')
                 ->where('ratings.rateable_type', Activity::class);
        })
        ->whereBetween('activity_bookings.created_at', $dateRange)
        ->groupBy('activities.id')
        ->orderByDesc('booking_count')
        ->limit(10)
        ->get();
    }

    private function getBookingTrends($dateRange)
    {
        return ActivityBooking::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as booking_count'),
            DB::raw('SUM(total_amount) as daily_revenue')
        )
        ->whereBetween('created_at', $dateRange)
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }

    private function getRevenueAnalysis($dateRange)
    {
        return ActivityBooking::select(
            'activities.name',
            DB::raw('COUNT(*) as booking_count'),
            DB::raw('SUM(activity_bookings.total_amount) as total_revenue'),
            DB::raw('AVG(activity_bookings.total_amount) as avg_revenue')
        )
        ->join('activities', 'activities.id', '=', 'activity_bookings.activity_id')
        ->whereBetween('activity_bookings.created_at', $dateRange)
        ->groupBy('activities.id', 'activities.name')
        ->orderByDesc('total_revenue')
        ->limit(10)
        ->get();
    }

    private function getCapacityUtilization($dateRange)
    {
        return Activity::select(
            'activities.*',
            DB::raw('COUNT(activity_bookings.id) as total_bookings'),
            DB::raw('(COUNT(activity_bookings.id) * 100.0 / activities.capacity) as utilization_rate')
        )
        ->leftJoin('activity_bookings', 'activities.id', '=', 'activity_bookings.activity_id')
        ->whereBetween('activity_bookings.created_at', $dateRange)
        ->groupBy('activities.id')
        ->orderByDesc('utilization_rate')
        ->get();
    }

    private function getRegistrationTrends($dateRange)
    {
        return Tourist::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as registration_count')
        )
        ->whereBetween('created_at', $dateRange)
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }

    private function getDemographicData()
    {
        return [
            'gender_distribution' => Tourist::select('gender', DB::raw('COUNT(*) as count'))
                ->groupBy('gender')
                ->get(),
            'age_distribution' => Tourist::select(
                DB::raw('CASE
                    WHEN age < 18 THEN "Under 18"
                    WHEN age BETWEEN 18 AND 24 THEN "18-24"
                    WHEN age BETWEEN 25 AND 34 THEN "25-34"
                    WHEN age BETWEEN 35 AND 44 THEN "35-44"
                    WHEN age BETWEEN 45 AND 54 THEN "45-54"
                    ELSE "55+" END as age_group'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('age_group')
            ->get(),
        ];
    }

    private function getEngagementMetrics($dateRange)
    {
        return [
            'ratings_per_user' => Rating::select('tourist_id', DB::raw('COUNT(*) as rating_count'))
                ->whereBetween('created_at', $dateRange)
                ->groupBy('tourist_id')
                ->orderByDesc('rating_count')
                ->limit(10)
                ->get(),
            'bookings_per_user' => ActivityBooking::select(
                'tourist_id',
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(total_amount) as total_spent')
            )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('tourist_id')
            ->orderByDesc('booking_count')
            ->limit(10)
            ->get(),
        ];
    }

    private function getPreferenceAnalysis()
    {
        return Cache::remember('preference_analysis', $this->cacheTime, function () {
            $preferences = DB::table('user_preferences')
                ->select(
                    'interests',
                    'preferred_categories',
                    'preferred_locations',
                    'preferred_price_range_min',
                    'preferred_price_range_max'
                )
                ->get();

            $analysis = [
                'interests' => [],
                'categories' => [],
                'locations' => [],
                'price_ranges' => [
                    'min' => 0,
                    'max' => 0,
                    'avg' => 0
                ]
            ];

            foreach ($preferences as $pref) {
                // Analyze interests
                if ($pref->interests) {
                    foreach (json_decode($pref->interests) as $interest) {
                        $analysis['interests'][$interest] = ($analysis['interests'][$interest] ?? 0) + 1;
                    }
                }

                // Analyze categories
                if ($pref->preferred_categories) {
                    foreach (json_decode($pref->preferred_categories) as $category) {
                        $analysis['categories'][$category] = ($analysis['categories'][$category] ?? 0) + 1;
                    }
                }

                // Analyze locations
                if ($pref->preferred_locations) {
                    foreach (json_decode($pref->preferred_locations) as $location) {
                        $analysis['locations'][$location] = ($analysis['locations'][$location] ?? 0) + 1;
                    }
                }

                // Analyze price ranges
                if ($pref->preferred_price_range_max > $analysis['price_ranges']['max']) {
                    $analysis['price_ranges']['max'] = $pref->preferred_price_range_max;
                }
                if ($pref->preferred_price_range_min < $analysis['price_ranges']['min']) {
                    $analysis['price_ranges']['min'] = $pref->preferred_price_range_min;
                }
            }

            // Sort by popularity
            arsort($analysis['interests']);
            arsort($analysis['categories']);
            arsort($analysis['locations']);

            return $analysis;
        });
    }

    private function getDateRange($timeframe)
    {
        $end = Carbon::now();
        $start = match($timeframe) {
            'week' => $end->copy()->subWeek(),
            'month' => $end->copy()->subMonth(),
            'quarter' => $end->copy()->subQuarter(),
            'year' => $end->copy()->subYear(),
            default => $end->copy()->subMonth(),
        };

        return [$start, $end];
    }
}
