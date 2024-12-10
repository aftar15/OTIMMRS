<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Session;
use Carbon\Carbon;

class AdminAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $sessionId = $request->header('Authorization');
        
        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'No session provided'
            ], 401);
        }

        $sessionId = str_replace('Bearer ', '', $sessionId);
        
        $session = Session::with('admin')
            ->where('id', $sessionId)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$session || !$session->admin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session'
            ], 401);
        }

        // Add admin to request for later use
        $request->admin = $session->admin;
        
        return $next($request);
    }
}
