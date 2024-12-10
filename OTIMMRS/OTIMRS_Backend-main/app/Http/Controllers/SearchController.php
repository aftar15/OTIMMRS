<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    protected $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function searchAttractions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'location' => 'nullable|string|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|gt:min_price',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:avg_rating,admission_fee,distance',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = $this->searchService->searchAttractions($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching attractions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchActivities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|gt:min_price',
            'min_capacity' => 'nullable|integer|min:1',
            'requires_booking' => 'nullable|boolean',
            'schedule_type' => 'nullable|in:one_time,recurring',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:start_time,cost,avg_rating,distance',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = $this->searchService->searchActivities($request->all());
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
