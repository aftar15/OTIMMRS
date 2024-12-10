<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Session;
use App\Models\Tourist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TouristAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->header('Authorization');
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No authentication token provided'
                ], 401);
            }

            // Remove Bearer prefix if present
            $token = str_replace('Bearer ', '', $token);
            
            // Try to find session by token
            $session = Session::with('tourist')
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            $tourist = $session->tourist;
            if (!$tourist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tourist not found'
                ], 404);
            }

            // Update last activity
            $session->last_activity = now()->timestamp;
            $session->save();

            // Store the tourist and session data in request attributes
            $request->attributes->set('tourist', $tourist);
            $request->attributes->set('session', $session);
            
            // Set auth guard
            auth()->setUser($tourist);
            
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Tourist auth middleware error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
