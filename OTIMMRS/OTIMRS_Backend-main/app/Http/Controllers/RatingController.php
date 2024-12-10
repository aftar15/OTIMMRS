<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\TouristAttraction;
use App\Models\Activity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function getTouristAttractionRating(Request $request)
    {
        $touristSession = $request->attributes->get('session');

        // Check if the session is valid and contains a tourist ID
        if (!$touristSession || !isset($touristSession->tourist_id)) {
            return response()->json(['success' => 0, 'message' => 'Invalid session'], 401);
        }

        $touristId = $touristSession->tourist_id;
        $touristAttractionId = $request->id;

        $rating = Rating::where('tourist_id', $touristId)
            ->where('rateable_type', TouristAttraction::class)
            ->where('rateable_id', $touristAttractionId)
            ->first();

        if ($rating) {
            return response()->json(['success' => 1, 'data' => $rating]);
        } else {
            return response()->json(['success' => 0, 'message' => 'No rating found for this tourist on this attraction']);
        }
    }

    public function getTouristActivityRating(Request $request)
    {
        $touristSession = $request->attributes->get('session');

        // Check if the session is valid and contains a tourist ID
        if (!$touristSession || !isset($touristSession->tourist_id)) {
            return response()->json(['success' => 0, 'message' => 'Invalid session'], 401);
        }

        $touristId = $touristSession->tourist_id;
        $touristActivityId = $request->id;

        $rating = Rating::where('tourist_id', $touristId)
            ->where('rateable_type', Activity::class)
            ->where('rateable_id', $touristActivityId)
            ->first();

        if ($rating) {
            return response()->json(['success' => 1, 'data' => $rating]);
        } else {
            return response()->json(['success' => 0, 'message' => 'No rating found for this tourist on this activity']);
        }
    }
}
