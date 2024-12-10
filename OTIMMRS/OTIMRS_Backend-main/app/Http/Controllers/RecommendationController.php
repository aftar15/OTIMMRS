<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function getRecommendedActivities(Request $request)
    {
        $limit = $request->input('limit', 10);
        $userId = Auth::id();

        $recommendations = $this->recommendationService->getRecommendedActivities($userId, $limit);

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    public function getRecommendedAttractions(Request $request)
    {
        $limit = $request->input('limit', 10);
        $userId = Auth::id();

        $recommendations = $this->recommendationService->getRecommendedAttractions($userId, $limit);

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    public function getPopularActivities(Request $request)
    {
        $limit = $request->input('limit', 10);
        $recommendations = $this->recommendationService->getPopularActivities($limit);

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    public function getPopularAttractions(Request $request)
    {
        $limit = $request->input('limit', 10);
        $recommendations = $this->recommendationService->getPopularAttractions($limit);

        return response()->json([
            'success' => true,
            'data' => $recommendations
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'interests' => 'nullable|array',
            'preferred_categories' => 'nullable|array',
            'preferred_locations' => 'nullable|array',
            'preferred_price_range_min' => 'nullable|numeric|min:0',
            'preferred_price_range_max' => 'nullable|numeric|min:0|gt:preferred_price_range_min',
        ]);

        $userId = Auth::id();
        $preferences = $this->recommendationService->updateUserPreferences($userId, $validated);

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    public function trackAction(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required',
            'item_type' => 'required|in:activity,attraction',
            'action' => 'required|in:view,book,rate,comment,search'
        ]);

        $userId = Auth::id();
        $this->recommendationService->trackUserAction(
            $userId,
            $validated['item_id'],
            $validated['item_type'],
            $validated['action']
        );

        return response()->json([
            'success' => true,
            'message' => 'Action tracked successfully'
        ]);
    }
}
