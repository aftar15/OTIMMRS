<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Session;
use Carbon\Carbon;

class CheckSessionAuth
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $sessionId = $request->header('Authorization') ?? $request->header('X-Session-ID');
            
            if (!$sessionId) {
                \Log::warning('No session ID provided', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'headers' => $request->headers->all()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No session ID provided',
                    'logout' => true
                ], 401);
            }

            // Remove Bearer prefix if present
            $sessionId = str_replace('Bearer ', '', $sessionId);

            $session = Session::with('admin')->where('id', $sessionId)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$session || !$session->admin) {
                \Log::warning('Invalid or expired session', [
                    'session_id' => $sessionId,
                    'url' => $request->fullUrl(),
                    'method' => $request->method()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired session',
                    'logout' => true
                ], 401);
            }

            // Update session expiry
            $session->expires_at = Carbon::now()->addDays(1);
            $session->save();

            // Add admin to request for controllers to use
            $request->admin = $session->admin;

            // Add session info to response headers
            $response = $next($request);
            $response->headers->set('X-Session-Expires', $session->expires_at->toIso8601String());

            return $response;
        } catch (\Exception $e) {
            \Log::error('Session authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication error',
                'logout' => true
            ], 401);
        }
    }
}
