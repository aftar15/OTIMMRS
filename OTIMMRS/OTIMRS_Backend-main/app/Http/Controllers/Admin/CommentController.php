<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Tourist;
use App\Models\TouristAttraction;
use App\Models\Activity;
use App\Models\Accommodation;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Comment::with('tourist')
                ->orderBy('created_at', 'desc');

            // Apply type filter
            if ($request->has('type') && $request->type !== 'all') {
                $query->where('commentable_type', $request->type);
            }

            // Apply date filter
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhere('transportation', 'like', "%{$search}%")
                        ->orWhere('services', 'like', "%{$search}%")
                        ->orWhere('road_problems', 'like', "%{$search}%")
                        ->orWhere('others', 'like', "%{$search}%")
                        ->orWhereHas('tourist', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            $comments = $query->paginate($request->input('per_page', 10));

            $formattedComments = $comments->map(function ($comment) {
                // Get destination name based on type
                $destinationName = '';
                switch ($comment->commentable_type) {
                    case 'attraction':
                        $attraction = TouristAttraction::find($comment->commentable_id);
                        $destinationName = $attraction ? $attraction->name : 'Unknown Attraction';
                        break;
                    case 'activity':
                        $activity = Activity::find($comment->commentable_id);
                        $destinationName = $activity ? $activity->name : 'Unknown Activity';
                        break;
                    case 'accommodation':
                        $accommodation = Accommodation::find($comment->commentable_id);
                        $destinationName = $accommodation ? $accommodation->name : 'Unknown Accommodation';
                        break;
                }

                return [
                    'id' => $comment->id,
                    'type' => $comment->commentable_type,
                    'destination_name' => $destinationName,
                    'tourist_name' => $comment->tourist ? $comment->tourist->name : 'Unknown Tourist',
                    'transportation' => $comment->transportation,
                    'transportation_fee' => $comment->transportation_fee,
                    'services' => $comment->services,
                    'road_problems' => $comment->road_problems,
                    'price_increase' => $comment->price_increase,
                    'others' => $comment->others,
                    'created_at' => $comment->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedComments,
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching comments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $comment = Comment::with('tourist')->findOrFail($id);
            
            // Get destination name based on type
            $destinationName = '';
            switch ($comment->commentable_type) {
                case 'attraction':
                    $attraction = TouristAttraction::find($comment->commentable_id);
                    $destinationName = $attraction ? $attraction->name : 'Unknown Attraction';
                    break;
                case 'activity':
                    $activity = Activity::find($comment->commentable_id);
                    $destinationName = $activity ? $activity->name : 'Unknown Activity';
                    break;
                case 'accommodation':
                    $accommodation = Accommodation::find($comment->commentable_id);
                    $destinationName = $accommodation ? $accommodation->name : 'Unknown Accommodation';
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $comment->id,
                    'type' => $comment->commentable_type,
                    'destination_name' => $destinationName,
                    'tourist_name' => $comment->tourist ? $comment->tourist->name : 'Unknown Tourist',
                    'transportation' => $comment->transportation,
                    'transportation_fee' => $comment->transportation_fee,
                    'services' => $comment->services,
                    'road_problems' => $comment->road_problems,
                    'price_increase' => $comment->price_increase,
                    'others' => $comment->others,
                    'created_at' => $comment->created_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching comment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting comment: ' . $e->getMessage()
            ], 500);
        }
    }
} 