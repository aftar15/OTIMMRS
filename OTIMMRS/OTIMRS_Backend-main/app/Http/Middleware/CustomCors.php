<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomCors
{
    public function handle(Request $request, Closure $next)
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-CSRF-TOKEN, X-Session-ID');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');    // 24 hours

        // Handle errors
        if ($response->getStatusCode() >= 500) {
            \Log::error('Server error in CORS middleware', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'headers' => $request->headers->all(),
                'response' => $response->getContent()
            ]);
        }

        return $response;
    }
}
