<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthenticateSession
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        $user = $this->authService->validateSession($token);

        if (!$user) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        // Add user to request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
