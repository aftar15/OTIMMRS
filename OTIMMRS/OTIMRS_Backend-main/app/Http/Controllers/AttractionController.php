<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\Comment;
use App\Models\Tourist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttractionController extends Controller
{
    public function getPopular()
    {
        try {
            // Get popular attractions based on ratings and views
            $attractions = Attraction::select('attractions.*')
                ->leftJoin('ratings', 'attractions.id', '=', 'ratings.attraction_id')
                ->groupBy('attractions.id')
                ->orderByRaw('COALESCE(AVG(ratings.rating), 0) DESC')
                ->orderBy('views', 'DESC')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $attractions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular attractions',
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
            $hobbies = $user ? $user->hobbies : [];

            // Ensure hobbies is an array
            if (!is_array($hobbies)) {
                $hobbies = [];
            }
            
            // Base query
            $query = Attraction::select([
                'attractions.*',
                DB::raw('COALESCE(AVG(ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT ratings.id) as rating_count')
            ])
            ->leftJoin('ratings', 'attractions.id', '=', 'ratings.attraction_id');

            // If user has hobbies, use them for recommendations
            if (!empty($hobbies)) {
                $query->where(function($q) use ($hobbies) {
                    foreach ($hobbies as $hobby) {
                        if (isset($hobby['name'])) {
                            $hobbyName = $hobby['name'];
                            $q->orWhere('attractions.category', 'LIKE', '%' . $hobbyName . '%')
                              ->orWhereRaw('JSON_CONTAINS(attractions.tags, ?)', ['"' . $hobbyName . '"']);
                        }
                    }
                });
            }

            // Group and order
            $attractions = $query->groupBy('attractions.id')
                ->orderByRaw('COALESCE(AVG(ratings.rating), 0) DESC')
                ->orderBy('views', 'DESC')
                ->paginate($perPage);

            // Transform data for response
            $items = $attractions->items();
            foreach ($items as &$item) {
                $item->average_rating = (float)$item->average_rating;
                $item->rating_count = (int)$item->rating_count;
                if (isset($item->tags) && is_string($item->tags)) {
                    $item->tags = json_decode($item->tags) ?? [];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'current_page' => $attractions->currentPage(),
                    'total' => $attractions->total(),
                    'per_page' => $attractions->perPage(),
                    'last_page' => $attractions->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getRecommended: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recommended attractions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getById($id)
    {
        try {
            $attraction = Attraction::findOrFail($id);
            
            // Increment views
            $attraction->increment('views');

            return response()->json([
                'status' => 'success',
                'data' => $attraction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch attraction details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addComment(Request $request, $id)
    {
        try {
            $request->validate([
                'comment' => 'required|string|max:500',
                'transportation' => 'nullable|string',
                'transportation_fee' => 'nullable|numeric',
                'services' => 'nullable|string',
                'road_problems' => 'nullable|string',
                'price_increase' => 'nullable|string',
                'others' => 'nullable|string'
            ]);

            $tourist = Auth::user();
            $attraction = Attraction::findOrFail($id);

            $comment = new Comment();
            $comment->tourist_id = $tourist->id;
            $comment->comment = $request->comment;
            $comment->transportation = $request->transportation;
            $comment->transportation_fee = $request->transportation_fee;
            $comment->services = $request->services;
            $comment->road_problems = $request->road_problems;
            $comment->price_increase = $request->price_increase;
            $comment->others = $request->others;

            $attraction->comments()->save($comment);

            return response()->json([
                'status' => 'success',
                'message' => 'Comment added successfully',
                'data' => $comment
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding comment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addRating(Request $request, $id)
    {
        try {
            $request->validate([
                'rating' => 'required|integer|between:1,5'
            ]);

            $tourist = auth()->user();
            $attraction = Attraction::findOrFail($id);
            
            $rating = $attraction->ratings()->updateOrCreate(
                ['tourist_id' => $tourist->id],
                ['rating' => $request->rating]
            );

            // Update average rating
            $avgRating = $attraction->ratings()->avg('rating');
            $attraction->update(['rating' => round($avgRating, 1)]);

            return response()->json([
                'status' => 'success',
                'message' => 'Rating added successfully',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRating($id)
    {
        try {
            $tourist = auth()->user();
            $attraction = Attraction::findOrFail($id);
            
            $averageRating = $attraction->ratings()->avg('rating');
            $userRating = $attraction->ratings()
                ->where('tourist_id', $tourist->id)
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'average_rating' => round($averageRating, 1),
                    'user_rating' => $userRating ? $userRating->rating : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAll(Request $request)
    {
        try {
            $perPage = $request->input('items_per_page', 10);
            $page = $request->input('page', 1);

            $attractions = Attraction::select([
                'attractions.*',
                DB::raw('COALESCE(AVG(ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT ratings.id) as rating_count')
            ])
            ->leftJoin('ratings', 'attractions.id', '=', 'ratings.attraction_id')
            ->groupBy([
                'attractions.id',
                'attractions.name',
                'attractions.description',
                'attractions.location',
                'attractions.category',
                'attractions.image_url',
                'attractions.views',
                'attractions.created_at',
                'attractions.updated_at'
            ])
            ->orderByRaw('COALESCE(AVG(ratings.rating), 0) DESC')
            ->orderBy('views', 'DESC')
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $attractions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch attractions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attractions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllForAdmin(Request $request)
    {
        try {
            $perPage = $request->input('items_per_page', 12);
            $page = $request->input('page', 1);

            $attractions = Attraction::select('attractions.*')
                ->orderBy('attractions.created_at', 'DESC')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $attractions->items(),
                'total' => $attractions->total(),
                'current_page' => $attractions->currentPage(),
                'last_page' => $attractions->lastPage()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch attractions for admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attractions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $attraction = Attraction::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $attraction
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch attraction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch attraction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $attraction = Attraction::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string',
                'category' => 'required|string',
                'contact_info' => 'nullable',
                'opening_hours' => 'nullable',
                'tags' => 'nullable',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'entrance_fee' => 'nullable|numeric',
                'map_source' => 'nullable|string',
                'contact_phone' => 'nullable|string',
                'image_url' => 'nullable|string',
            ]);

            // Map entrance_fee to admission_fee
            if (isset($validatedData['entrance_fee'])) {
                $validatedData['admission_fee'] = $validatedData['entrance_fee'];
                unset($validatedData['entrance_fee']);
            }

            // Handle JSON fields if they are strings
            if (is_string($validatedData['contact_info'])) {
                $validatedData['contact_info'] = json_decode($validatedData['contact_info'], true);
            }
            if (is_string($validatedData['opening_hours'])) {
                $validatedData['opening_hours'] = json_decode($validatedData['opening_hours'], true);
            }
            if (is_string($validatedData['tags'])) {
                $validatedData['tags'] = json_decode($validatedData['tags'], true);
            }

            // Convert arrays back to JSON for storage
            if (isset($validatedData['contact_info'])) {
                $validatedData['contact_info'] = json_encode($validatedData['contact_info']);
            }
            if (isset($validatedData['opening_hours'])) {
                $validatedData['opening_hours'] = json_encode($validatedData['opening_hours']);
            }
            if (isset($validatedData['tags'])) {
                $validatedData['tags'] = json_encode($validatedData['tags']);
            }

            // Remove any null values to prevent overwriting existing data
            $validatedData = array_filter($validatedData, function($value) {
                return !is_null($value);
            });

            Log::info('Updating attraction', [
                'attraction_id' => $id,
                'data' => $validatedData
            ]);

            $attraction->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Attraction updated successfully',
                'data' => $attraction
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attraction not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'attraction_id' => $id,
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update attraction', [
                'attraction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update attraction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $attraction = Attraction::findOrFail($id);
            
            // Delete related records first
            $attraction->ratings()->delete();
            $attraction->comments()->delete();
            
            // Delete the attraction
            $attraction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attraction deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete attraction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attraction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getComments($id)
    {
        try {
            $attraction = Attraction::findOrFail($id);
            $comments = $attraction->comments()
                ->with(['tourist:id,full_name,email']) // Only include necessary tourist fields
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $comments
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attraction not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch attraction comments', [
                'attraction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadImage(Request $request, $id)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' // Increased max size to 5MB
            ]);

            $attraction = Attraction::findOrFail($id);

            // Delete old image if it exists
            if ($attraction->image_url) {
                $oldPath = str_replace(url('storage/'), '', $attraction->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Generate a unique filename
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            
            // Store the new image
            $path = $request->file('image')->storeAs(
                'attractions',
                $filename,
                'public'
            );
            
            // Create the full URL
            $fullUrl = url('storage/' . $path);
            
            // Update attraction with new image URL
            $attraction->update([
                'image_url' => $fullUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'image_url' => $fullUrl
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attraction not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image file',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to upload image', [
                'attraction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
