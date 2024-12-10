<?php

namespace App\Http\Controllers;

use App\Models\TouristAttraction;
use App\Models\Rating;
use App\Models\Comment;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TouristAttractionController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = TouristAttraction::query();

        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has(['latitude', 'longitude'])) {
            $radius = $request->get('radius', 10); // Default 10km radius
            $query->nearby($request->latitude, $request->longitude, $radius);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'distance' && $request->has(['latitude', 'longitude'])) {
            $query->orderByRaw("
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))))
            ", [$request->latitude, $request->longitude, $request->latitude]);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'category' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'opening_hours' => 'required|string',
            'admission_fee' => 'required|numeric',
            'image' => 'required|image|max:5120', // 5MB max
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'tags' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Handle image upload
            $imageUrls = $this->imageService->uploadImage($request->file('image'));

            // Create attraction
            $attraction = TouristAttraction::create([
                ...$request->except('image'),
                'image_url' => $imageUrls['main_image'],
                'thumbnail_url' => $imageUrls['thumbnail']
            ]);

            DB::commit();
            return response()->json($attraction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create attraction'], 500);
        }
    }

    public function show($id)
    {
        $attraction = TouristAttraction::with(['ratings', 'comments.user'])
            ->findOrFail($id);

        return response()->json($attraction);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $attraction = TouristAttraction::findOrFail($id);

            $data = $request->except('image');
            
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($attraction->image_url) {
                    $this->imageService->deleteImage($attraction->image_url);
                }
                
                // Upload new image
                $imageUrls = $this->imageService->uploadImage($request->file('image'));
                $data['image_url'] = $imageUrls['main_image'];
                $data['thumbnail_url'] = $imageUrls['thumbnail'];
            }

            $attraction->update($data);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attraction updated successfully',
                'data' => $attraction
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update attraction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attraction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $attraction = TouristAttraction::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete images
            $this->imageService->deleteImage($attraction->image_url);

            // Delete attraction
            $attraction->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete attraction'], 500);
        }
    }

    public function addRating(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attraction = TouristAttraction::findOrFail($id);

        $rating = $attraction->ratings()->create([
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json($rating, 201);
    }

    public function addComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tourist_attraction_id' => 'required|uuid',
                'transportation' => 'required|string',
                'transportation_fee' => 'required|numeric',
                'services' => 'required|string',
                'road_problems' => 'required|string',
                'price_increase' => 'required|string',
                'others' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $touristSession = $request->attributes->get('session');
            if (!$touristSession || !isset($touristSession->tourist_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid session'
                ], 401);
            }

            $attraction = TouristAttraction::findOrFail($request->tourist_attraction_id);

            // Format all fields into a single comment
            $commentText = "Transportation: {$request->transportation}\n" .
                         "Transportation Fee: {$request->transportation_fee}\n" .
                         "Services: {$request->services}\n" .
                         "Road Problems: {$request->road_problems}\n" .
                         "Price Increase: {$request->price_increase}";

            if ($request->others) {
                $commentText .= "\nOthers: {$request->others}";
            }

            // Create comment using the new Comment model
            $comment = Comment::create([
                'tourist_id' => $touristSession->tourist_id,
                'commentable_id' => $request->tourist_attraction_id,
                'commentable_type' => 'attraction',
                'comment' => $commentText,
                'transportation' => $request->transportation,
                'transportation_fee' => floatval($request->transportation_fee),
                'services' => $request->services,
                'road_problems' => $request->road_problems,
                'price_increase' => $request->price_increase,
                'others' => $request->others
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $comment
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in TouristAttractionController@addComment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = TouristAttraction::query();

        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('tags', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has(['latitude', 'longitude'])) {
            $radius = $request->get('radius', 10);
            $query->nearby($request->latitude, $request->longitude, $radius);
        }

        return $query->paginate($request->get('per_page', 15));
    }

    public function getRating(Request $request, $id)
    {
        try {
            $touristSession = $request->attributes->get('session');
            if (!$touristSession || !isset($touristSession->tourist_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid session'
                ], 401);
            }

            $attraction = TouristAttraction::findOrFail($id);
            
            // Get average rating
            $averageRating = $attraction->ratings()
                ->avg('rating') ?? 0;

            // Get user's rating if exists
            $userRating = $attraction->ratings()
                ->where('tourist_id', $touristSession->tourist_id)
                ->value('rating');

            // Get total number of ratings
            $totalRatings = $attraction->ratings()->count();

            return response()->json([
                'success' => true,
                'message' => 'Rating retrieved successfully',
                'data' => [
                    'average_rating' => round($averageRating, 1),
                    'user_rating' => $userRating,
                    'total_ratings' => $totalRatings
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error in TouristAttractionController@getRating: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting rating: ' . $e->getMessage()
            ], 500);
        }
    }
}
