<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;

class AccommodationController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $perPage = $request->input('items_per_page', 10);
            $page = $request->input('page', 1);

            $accommodations = Accommodation::select([
                'accommodations.*',
                DB::raw('COALESCE(AVG(accommodation_ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT accommodation_ratings.id) as rating_count')
            ])
            ->leftJoin('accommodation_ratings', 'accommodations.id', '=', 'accommodation_ratings.accommodation_id')
            ->groupBy([
                'accommodations.id',
                'accommodations.name',
                'accommodations.description',
                'accommodations.location',
                'accommodations.category',
                'accommodations.image_url',
                'accommodations.price_per_night',
                'accommodations.capacity',
                'accommodations.amenities',
                'accommodations.contact_info',
                'accommodations.views',
                'accommodations.created_at',
                'accommodations.updated_at'
            ])
            ->orderBy('accommodations.created_at', 'DESC')
            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $accommodations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch accommodations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch accommodations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRecommended(Request $request)
    {
        try {
            $perPage = $request->input('items_per_page', 10);
            $page = $request->input('page', 1);

            // Get user preferences
            $user = auth()->user();
            $hobbies = $user ? json_decode($user->hobbies, true) : [];
            
            // Extract categories from hobbies
            $categories = [];
            foreach ($hobbies as $hobby) {
                if (isset($hobby['categories'])) {
                    $categories = array_merge($categories, $hobby['categories']);
                }
            }
            $categories = array_unique($categories);

            // Base query
            $query = Accommodation::select([
                'accommodations.*',
                DB::raw('COALESCE(AVG(accommodation_ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT accommodation_ratings.id) as rating_count')
            ])
            ->leftJoin('accommodation_ratings', function($join) {
                $join->on('accommodations.id', '=', 'accommodation_ratings.accommodation_id')
                    ->whereNotNull('accommodation_ratings.accommodation_id');
            });

            // If user has preferences, use them for recommendations
            if (!empty($categories)) {
                $query->where(function($q) use ($categories) {
                    foreach ($categories as $category) {
                        $q->orWhere('accommodations.category', 'LIKE', '%' . $category . '%');
                    }
                });
            }

            // Group and order
            $accommodations = $query->groupBy([
                'accommodations.id',
                'accommodations.name',
                'accommodations.description',
                'accommodations.location',
                'accommodations.category',
                'accommodations.image_url',
                'accommodations.price_per_night',
                'accommodations.capacity',
                'accommodations.amenities',
                'accommodations.contact_info',
                'accommodations.views',
                'accommodations.created_at',
                'accommodations.updated_at'
            ])
            ->orderByRaw('COALESCE(AVG(accommodation_ratings.rating), 0) DESC')
            ->orderBy('views', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $accommodations
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch recommended accommodations', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recommended accommodations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getById($id)
    {
        try {
            $accommodation = Accommodation::select([
                'accommodations.*',
                DB::raw('COALESCE(AVG(accommodation_ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT accommodation_ratings.id) as rating_count')
            ])
            ->leftJoin('accommodation_ratings', function($join) {
                $join->on('accommodations.id', '=', 'accommodation_ratings.accommodation_id')
                    ->whereNotNull('accommodation_ratings.accommodation_id');
            })
            ->where('accommodations.id', $id)
            ->groupBy([
                'accommodations.id',
                'accommodations.name',
                'accommodations.description',
                'accommodations.location',
                'accommodations.category',
                'accommodations.image_url',
                'accommodations.price_per_night',
                'accommodations.capacity',
                'accommodations.amenities',
                'accommodations.contact_info',
                'accommodations.views',
                'accommodations.created_at',
                'accommodations.updated_at'
            ])
            ->firstOrFail();

            // Increment views
            $accommodation->increment('views');

            return response()->json([
                'success' => true,
                'data' => $accommodation
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch accommodation details', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch accommodation details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRating($id)
    {
        try {
            $tourist = auth()->user();
            $accommodation = Accommodation::findOrFail($id);
            
            // Get the user's rating
            $userRating = $accommodation->ratings()
                ->where('tourist_id', $tourist->id)
                ->first();

            // Get the average rating
            $averageRating = $accommodation->ratings()->avg('rating');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user_rating' => $userRating ? $userRating->rating : null,
                    'average_rating' => round($averageRating, 1)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch accommodation rating', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addRating(Request $request, $id)
    {
        try {
            $request->validate([
                'rating' => 'required|numeric|between:1,5',
                'comment' => 'nullable|string|max:1000'
            ]);

            $tourist = auth()->user();
            $accommodation = Accommodation::findOrFail($id);
            
            // Create or update the rating
            $rating = $accommodation->ratings()->updateOrCreate(
                ['tourist_id' => $tourist->id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Rating and comment added successfully',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add accommodation rating', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getComments($id)
    {
        try {
            $accommodation = Accommodation::findOrFail($id);
            
            $comments = $accommodation->ratings()
                ->with('tourist:id,first_name,last_name')
                ->whereNotNull('comment')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'tourist_id', 'rating', 'comment', 'created_at']);

            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch accommodation comments', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllForAdmin(Request $request)
    {
        try {
            $perPage = $request->input('items_per_page', 12);
            $page = $request->input('page', 1);

            $accommodations = Accommodation::select([
                'accommodations.*',
                DB::raw('COALESCE(AVG(accommodation_ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT accommodation_ratings.id) as rating_count')
            ])
            ->leftJoin('accommodation_ratings', 'accommodations.id', '=', 'accommodation_ratings.accommodation_id')
            ->groupBy([
                'accommodations.id',
                'accommodations.name',
                'accommodations.description',
                'accommodations.location',
                'accommodations.category',
                'accommodations.image_url',
                'accommodations.price_per_night',
                'accommodations.capacity',
                'accommodations.amenities',
                'accommodations.contact_info',
                'accommodations.views',
                'accommodations.created_at',
                'accommodations.updated_at'
            ])
            ->orderBy('accommodations.created_at', 'DESC')
            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $accommodations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch accommodations for admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch accommodations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadImage(Request $request, $id)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $accommodation = Accommodation::findOrFail($id);

            // Delete old image if it exists
            if ($accommodation->image_url) {
                $oldPath = str_replace('/storage/', '', $accommodation->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Store the new image
            $path = $request->file('image')->store('accommodations', 'public');
            
            // Create the full URL
            $fullUrl = url('storage/' . $path);
            
            // Update accommodation with new image URL
            $accommodation->update([
                'image_url' => $fullUrl
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully',
                'data' => [
                    'image_url' => $fullUrl
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Image validation failed', [
                'error' => $e->getMessage(),
                'validation_errors' => $e->errors()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid image file',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to upload image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $accommodation = Accommodation::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $accommodation
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch accommodation details', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch accommodation details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'address' => 'required|string',
                'contact_number' => 'required|string',
                'accommodation_type' => 'required|string',
                'price_range' => 'required|string',
                'amenities' => 'nullable|array',
                'check_in_time' => 'required|date_format:H:i',
                'check_out_time' => 'required|date_format:H:i',
                'status' => 'required|in:active,inactive',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Start a database transaction
            DB::beginTransaction();

            // Create the accommodation
            $accommodation = Accommodation::create([
                'name' => $request->name,
                'description' => $request->description,
                'address' => $request->address,
                'contact_number' => $request->contact_number,
                'accommodation_type' => $request->accommodation_type,
                'price_range' => $request->price_range,
                'amenities' => $request->amenities,
                'check_in_time' => $request->check_in_time,
                'check_out_time' => $request->check_out_time,
                'status' => $request->status
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('accommodations', 'public');
                $fullUrl = url('storage/' . $path);
                $accommodation->update(['image_url' => $fullUrl]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Accommodation created successfully',
                'data' => $accommodation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create accommodation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create accommodation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'address' => 'sometimes|required|string',
                'contact_number' => 'sometimes|required|string',
                'accommodation_type' => 'sometimes|required|string',
                'price_range' => 'sometimes|required|string',
                'amenities' => 'nullable|array',
                'check_in_time' => 'sometimes|required|date_format:H:i',
                'check_out_time' => 'sometimes|required|date_format:H:i',
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $accommodation = Accommodation::findOrFail($id);

            // Start a database transaction
            DB::beginTransaction();

            // Update the accommodation
            $accommodation->update($request->only([
                'name',
                'description',
                'address',
                'contact_number',
                'accommodation_type',
                'price_range',
                'amenities',
                'check_in_time',
                'check_out_time',
                'status'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Accommodation updated successfully',
                'data' => $accommodation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update accommodation', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update accommodation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $accommodation = Accommodation::findOrFail($id);

            // Start a database transaction
            DB::beginTransaction();

            // Delete old image if it exists
            if ($accommodation->image_url) {
                $oldPath = str_replace('/storage/', '', $accommodation->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Delete the accommodation
            $accommodation->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Accommodation deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete accommodation', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete accommodation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'accommodation_id' => 'required|uuid',
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

            $accommodation = Accommodation::findOrFail($request->accommodation_id);

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
                'commentable_id' => $request->accommodation_id,
                'commentable_type' => 'accommodation',
                'comment' => $commentText,
                'transportation' => $request->transportation,
                'transportation_fee' => $request->transportation_fee,
                'services' => $request->services,
                'road_problems' => $request->road_problems,
                'price_increase' => $request->price_increase,
                'others' => $request->others
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $comment
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in AccommodationController@addComment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding comment: ' . $e->getMessage()
            ], 500);
        }
    }
}
