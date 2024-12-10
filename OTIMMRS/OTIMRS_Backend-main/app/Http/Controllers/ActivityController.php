<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Rating;
use App\Models\Comment;

class ActivityController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = Activity::query();

        // Apply filters
        if ($request->has('status')) {
            if ($request->status === 'upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'available') {
                $query->available();
            }
        }

        if ($request->has(['latitude', 'longitude'])) {
            $radius = $request->get('radius', 10);
            $query->nearby($request->latitude, $request->longitude, $radius);
        }

        if ($request->has('date')) {
            $date = Carbon::parse($request->date);
            $query->where(function($q) use ($date) {
                $q->where(function($q) use ($date) {
                    $q->where('schedule_type', 'one_time')
                      ->whereDate('start_time', $date);
                })->orWhere(function($q) use ($date) {
                    $q->where('schedule_type', 'recurring')
                      ->where('start_time', '<=', $date)
                      ->where(function($q) use ($date) {
                          $q->whereNull('end_time')
                            ->orWhere('end_time', '>=', $date);
                      });
                });
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'start_time');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 15));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string',
                'category' => 'required|string',
                'tags' => 'required|array',
                'image_url' => 'required|url',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|string',
                'difficulty' => 'required|string',
                'included_items' => 'required|array',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'schedule_type' => 'required|string|in:one_time,daily,weekly,monthly',
                'recurring_pattern' => 'required_unless:schedule_type,one_time|array',
                'cost' => 'required|numeric|min:0',
                'capacity' => 'required|integer|min:1',
                'min_participants' => 'required|integer|min:1|lte:capacity',
                'is_active' => 'required|boolean',
                'requires_booking' => 'required|boolean',
                'booking_deadline_hours' => 'required|integer|min:0',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'map_source' => 'required|string',
                'thumbnail_url' => 'required|url',
                'contact_number' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            $activity = Activity::create(array_merge(
                $request->all(),
                ['id' => Str::uuid()]
            ));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activity created successfully',
                'data' => $activity
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create activity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $activity = Activity::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $activity->name,
                    'description' => $activity->description,
                    'location' => $activity->location,
                    'due_date' => $activity->due_date ? $activity->due_date->format('Y-m-d') : null,
                    'cost' => $activity->cost,
                    'capacity' => $activity->capacity,
                    'rating' => $activity->rating,
                    'latitude' => $activity->latitude,
                    'longitude' => $activity->longitude,
                    'image_url' => $activity->image_url,
                    'map_source' => $activity->map_source,
                    'contact_phone' => $activity->contact_phone,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Activity not found', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Activity not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to fetch activity', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $activity = Activity::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'location' => 'required|string',
                'due_date' => 'nullable|date',
                'cost' => 'required|numeric|min:0',
                'capacity' => 'required|integer|min:1',
                'image_url' => 'nullable|string',
                'map_source' => 'nullable|string',
                'contact_phone' => 'nullable|string'
            ]);

            // Map frontend field names to database field names
            $dataToUpdate = [
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'location' => $validatedData['location'],
                'due_date' => $validatedData['due_date'],
                'cost' => $validatedData['cost'],
                'capacity' => $validatedData['capacity'],
                'image_url' => $validatedData['image_url'],
                'map_source' => $validatedData['map_source'],
                'contact_phone' => $validatedData['contact_phone']
            ];

            $activity->update($dataToUpdate);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
                'data' => $activity->fresh()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Activity validation failed', [
                'id' => $id,
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update activity', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update activity: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete images
            $this->imageService->deleteImage($activity->image_url);

            // Delete activity
            $activity->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete activity'], 500);
        }
    }

    public function book(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        if (!$activity->isBookable()) {
            return response()->json(['error' => 'Activity is not available for booking'], 422);
        }

        $validator = Validator::make($request->all(), [
            'scheduled_date' => 'required|date',
            'number_of_participants' => "required|integer|min:1|max:{$activity->available_slots}",
            'special_requests' => 'nullable|string',
            'contact_number' => 'required|string',
            'contact_email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $booking = ActivityBooking::create([
                'activity_id' => $activity->id,
                'user_id' => auth()->id(),
                ...$request->all()
            ]);

            return response()->json($booking, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create booking'], 500);
        }
    }

    public function cancelBooking($id, $bookingId)
    {
        $booking = ActivityBooking::where('activity_id', $id)
            ->where('id', $bookingId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($booking->scheduled_date <= Carbon::now()) {
            return response()->json(['error' => 'Cannot cancel past bookings'], 422);
        }

        $booking->update(['status' => 'cancelled']);
        return response()->json($booking);
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

            $activity = Activity::findOrFail($id);
            
            // Get average rating
            $averageRating = $activity->ratings()
                ->avg('rating') ?? 0;

            // Get user's rating if exists
            $userRating = $activity->ratings()
                ->where('tourist_id', $touristSession->tourist_id)
                ->value('rating');

            // Get total number of ratings
            $totalRatings = $activity->ratings()->count();

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
            \Log::error('Error in ActivityController@getRating: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting rating: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addRating(Request $request, $id)
    {
        try {
            Log::info('Adding rating', [
                'activity_id' => $id,
                'request_data' => $request->all(),
                'session' => $request->attributes->get('session')
            ]);

            $validator = Validator::make($request->all(), [
                'rating' => 'required|numeric|between:1,5',
                'comment' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                Log::error('Rating validation failed', [
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $touristSession = $request->attributes->get('session');
            if (!$touristSession || !isset($touristSession->tourist_id)) {
                Log::error('Invalid session for rating', [
                    'session' => $touristSession
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid session'
                ], 401);
            }

            $activity = Activity::findOrFail($id);

            // Generate a UUID for the rating
            $ratingId = Str::uuid();
            Log::info('Generated rating UUID', ['rating_id' => $ratingId]);

            // Create or update rating
            DB::table('activity_ratings')->updateOrInsert(
                [
                    'tourist_id' => $touristSession->tourist_id,
                    'activity_id' => $id
                ],
                [
                    'id' => $ratingId,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );

            Log::info('Rating saved successfully');

            // Get the updated rating
            $rating = DB::table('activity_ratings')
                ->where('tourist_id', $touristSession->tourist_id)
                ->where('activity_id', $id)
                ->first();

            // Update activity average rating
            $avgRating = DB::table('activity_ratings')
                ->where('activity_id', $id)
                ->avg('rating');

            $activity->update(['rating' => round($avgRating, 1)]);

            Log::info('Activity average rating updated', [
                'activity_id' => $id,
                'new_avg_rating' => round($avgRating, 1)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rating added successfully',
                'data' => $rating
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to add activity rating', [
                'activity_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all activities for admin with pagination
     */
    public function getAllForAdmin(Request $request)
    {
        try {
            $perPage = $request->input('items_per_page', 12);
            $page = $request->input('page', 1);
            $search = $request->input('search', '');

            $query = Activity::select([
                'activities.*',
                DB::raw('COALESCE(AVG(activity_ratings.rating), 0) as average_rating'),
                DB::raw('COUNT(DISTINCT activity_ratings.id) as rating_count')
            ])
            ->leftJoin('activity_ratings', 'activities.id', '=', 'activity_ratings.activity_id')
            ->groupBy(
                'activities.id',
                'activities.name',
                'activities.description',
                'activities.location',
                'activities.category',
                'activities.tags',
                'activities.image_url',
                'activities.rating',
                'activities.price',
                'activities.duration',
                'activities.difficulty',
                'activities.included_items',
                'activities.start_time',
                'activities.end_time',
                'activities.schedule_type',
                'activities.recurring_pattern',
                'activities.cost',
                'activities.capacity',
                'activities.min_participants',
                'activities.is_active',
                'activities.requires_booking',
                'activities.booking_deadline_hours',
                'activities.latitude',
                'activities.longitude',
                'activities.map_source',
                'activities.thumbnail_url',
                'activities.contact_number',
                'activities.created_at',
                'activities.updated_at'
            );

            // Apply search if provided
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('activities.name', 'LIKE', "%{$search}%")
                      ->orWhere('activities.description', 'LIKE', "%{$search}%")
                      ->orWhere('activities.location', 'LIKE', "%{$search}%")
                      ->orWhere('activities.category', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            $sortField = $request->input('sort_field', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            
            // Validate sort field
            $allowedSortFields = ['name', 'location', 'category', 'created_at', 'average_rating'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'created_at';
            }

            if ($sortField === 'average_rating') {
                $query->orderBy(DB::raw('COALESCE(AVG(activity_ratings.rating), 0)'), $sortOrder);
            } else {
                $query->orderBy("activities.{$sortField}", $sortOrder);
            }

            $activities = $query->paginate($perPage);

            // Transform the data to handle JSON fields
            $transformedData = $activities->getCollection()->map(function ($activity) {
                $activity->tags = json_decode($activity->tags) ?? [];
                $activity->included_items = json_decode($activity->included_items) ?? [];
                $activity->recurring_pattern = json_decode($activity->recurring_pattern) ?? null;
                return $activity;
            });

            $activities->setCollection($transformedData);

            return response()->json([
                'success' => true,
                'data' => $activities->items(),
                'total' => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch activities for admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new activity
     */
    public function storeAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:0',
                'capacity' => 'required|integer|min:1',
                'location' => 'required|string',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $activity = new Activity($request->except('image'));

            if ($request->hasFile('image')) {
                $activity->image_url = $this->imageService->uploadImage($request->file('image'), 'activities');
            }

            $activity->save();

            return response()->json([
                'success' => true,
                'message' => 'Activity created successfully',
                'data' => $activity
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific activity
     */
    public function showAdmin($id)
    {
        try {
            $activity = Activity::with(['bookings' => function($query) {
                $query->where('scheduled_date', '>=', now());
            }])->findOrFail($id);
            
            // Transform any JSON fields
            $activity->tags = json_decode($activity->tags) ?? [];
            $activity->included_items = json_decode($activity->included_items) ?? [];
            $activity->recurring_pattern = json_decode($activity->recurring_pattern) ?? null;
            
            return response()->json([
                'success' => true,
                'data' => $activity
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch activity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Activity not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update an activity
     */
    public function updateAdmin(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category_id' => 'sometimes|exists:categories,id',
                'price' => 'sometimes|numeric|min:0',
                'duration' => 'sometimes|integer|min:0',
                'capacity' => 'sometimes|integer|min:1',
                'location' => 'sometimes|string',
                'latitude' => 'sometimes|numeric',
                'longitude' => 'sometimes|numeric',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $activity->fill($request->except('image'));

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($activity->image_url) {
                    $this->imageService->deleteImage($activity->image_url);
                }
                $activity->image_url = $this->imageService->uploadImage($request->file('image'), 'activities');
            }

            $activity->save();

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
                'data' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an activity
     */
    public function destroyAdmin($id)
    {
        try {
            $activity = Activity::findOrFail($id);

            // Delete image if exists
            if ($activity->image_url) {
                $this->imageService->deleteImage($activity->image_url);
            }

            $activity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Activity deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getComments($id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $comments = $activity->comments()->with('tourist')->get();

            return response()->json([
                'success' => true,
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch activity comments', [
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

    public function getAll(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $query = Activity::query()
                ->select([
                    'id',
                    'name',
                    'description',
                    'location',
                    'image_url',
                    'rating',
                    'latitude',
                    'longitude',
                    'map_source',
                    'is_active',
                    'created_at'
                ])
                ->where('is_active', true)
                ->orderBy('created_at', 'desc');

            $total = $query->count();
            
            $activities = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'name' => $activity->name,
                        'description' => $activity->description,
                        'location' => $activity->location,
                        'image_url' => $activity->image_url,
                        'ratings_avg_rating' => $activity->rating,
                        'latitude' => $activity->latitude,
                        'longitude' => $activity->longitude,
                        'map_source' => $activity->map_source
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => [
                    'data' => $activities,
                    'total' => $total,
                    'page' => (int)$page,
                    'perPage' => (int)$perPage,
                    'lastPage' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch activities', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPopular(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $itemsPerPage = $request->query('items_per_page', 10);

            $query = Activity::query()
                ->select(['activities.*'])
                ->leftJoin('activity_ratings', 'activities.id', '=', 'activity_ratings.activity_id')
                ->groupBy('activities.id')
                ->orderByRaw('COALESCE(AVG(activity_ratings.rating), 0) DESC')
                ->orderBy('activities.created_at', 'DESC');

            $activities = $query->paginate($itemsPerPage, ['*'], 'page', $page);

            // Transform the data to ensure consistent format
            $transformedData = $activities->items();
            foreach ($transformedData as &$activity) {
                if (is_object($activity)) {
                    $activity = $activity->toArray();
                }
            }

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'meta' => [
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total()
                ]
            ], 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            \Log::error('Error in getPopular: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch popular activities',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }

    public function getRecommended(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $query = Activity::query()
                ->select([
                    'id',
                    'name',
                    'description',
                    'location',
                    'image_url',
                    'rating',
                    'latitude',
                    'longitude',
                    'map_source',
                    'is_active',
                    'created_at'
                ])
                ->where('is_active', true)
                ->orderBy('rating', 'desc')
                ->orderBy('created_at', 'desc');

            $total = $query->count();
            
            $activities = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'name' => $activity->name,
                        'description' => $activity->description,
                        'location' => $activity->location,
                        'image_url' => $activity->image_url,
                        'ratings_avg_rating' => $activity->rating,
                        'latitude' => $activity->latitude,
                        'longitude' => $activity->longitude,
                        'map_source' => $activity->map_source
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Recommended activities retrieved successfully',
                'data' => [
                    'data' => $activities,
                    'total' => $total,
                    'page' => (int)$page,
                    'perPage' => (int)$perPage,
                    'lastPage' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch recommended activities', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recommended activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addComment(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
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

            $activity = Activity::findOrFail($id);

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
                'commentable_id' => $id,
                'commentable_type' => 'activity',
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
            \Log::error('Error in ActivityController@addComment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding comment: ' . $e->getMessage()
            ], 500);
        }
    }
}
