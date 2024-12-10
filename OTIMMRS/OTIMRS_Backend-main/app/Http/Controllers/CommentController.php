<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $type = $request->query('type');
            $date = $request->query('date');
            $search = $request->query('search');

            $query = Comment::with(['tourist', 'commentable'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($type && $type !== 'all') {
                $query->where('commentable_type', 'LIKE', '%' . $type . '%');
            }

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('comment', 'LIKE', '%' . $search . '%')
                      ->orWhere('transportation', 'LIKE', '%' . $search . '%')
                      ->orWhere('services', 'LIKE', '%' . $search . '%')
                      ->orWhere('road_problems', 'LIKE', '%' . $search . '%')
                      ->orWhere('price_increase', 'LIKE', '%' . $search . '%')
                      ->orWhere('others', 'LIKE', '%' . $search . '%');
                });
            }

            $comments = $query->paginate($perPage);

            $formattedComments = $comments->through(function ($comment) {
                $commentable = $comment->commentable;
                $tourist = $comment->tourist;
                
                return [
                    'id' => $comment->id,
                    'type' => strtolower(class_basename($comment->commentable_type)),
                    'destination_name' => $commentable ? $commentable->name : 'Unknown',
                    'tourist_name' => $tourist ? $tourist->name : 'Anonymous',
                    'transportation' => $comment->transportation,
                    'transportation_fee' => $comment->transportation_fee,
                    'services' => $comment->services,
                    'road_problems' => $comment->road_problems,
                    'price_increase' => $comment->price_increase,
                    'others' => $comment->others,
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formattedComments->items(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching comments: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch comments'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $comment = Comment::with(['tourist', 'commentable'])->findOrFail($id);
            $commentable = $comment->commentable;
            
            $formattedComment = [
                'id' => $comment->id,
                'type' => strtolower(class_basename($comment->commentable_type)),
                'destination_name' => $commentable ? $commentable->name : 'Unknown',
                'tourist_name' => $comment->tourist ? $comment->tourist->name : 'Unknown Tourist',
                'comment' => $comment->comment,
                'transportation' => $comment->transportation,
                'transportation_fee' => $comment->transportation_fee,
                'services' => $comment->services,
                'road_problems' => $comment->road_problems,
                'price_increase' => $comment->price_increase,
                'others' => $comment->others,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedComment
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching comment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $comment->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete comment'
            ], 500);
        }
    }
}
