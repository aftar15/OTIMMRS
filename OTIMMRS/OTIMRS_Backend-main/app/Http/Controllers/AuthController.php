<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Session;
use App\Models\Tourist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            Log::info('Admin login attempt', ['username' => $request->username]);

            // Validate request
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string'
            ]);

            // Find admin
            $admin = Admin::where('username', $request->username)->first();
            
            if (!$admin || !Hash::check($request->password, $admin->password)) {
                Log::warning('Invalid login attempt', ['username' => $request->username]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            DB::beginTransaction();

            try {
                // Invalidate old sessions
                $admin->invalidateAllSessions();

                // Create new session
                $session = new Session([
                    'admin_id' => $admin->id,
                    'expires_at' => Carbon::now()->addHours(24),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'payload' => json_encode([
                        'last_login' => Carbon::now()->toISOString(),
                        'login_type' => 'admin'
                    ])
                ]);

                $session->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Hide sensitive data
            $admin = $admin->makeHidden(['password']);

            Log::info('Admin login successful', ['username' => $request->username]);

            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => $admin,
                    'session_id' => $session->id,
                    'expires_at' => $session->expires_at->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Admin login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'username' => $request->username
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $sessionId = $request->header('Authorization') ?? $request->header('X-Session-ID');
            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No session ID provided'
                ], 401);
            }

            // Remove Bearer prefix if present
            $sessionId = str_replace('Bearer ', '', $sessionId);

            $session = Session::with('admin')->where('id', $sessionId)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$session || !$session->admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired session'
                ], 401);
            }

            // Update session activity
            $session->updateLastActivity();

            $admin = $session->admin->makeHidden(['password']);

            return response()->json([
                'success' => true,
                'data' => $admin,
                'session' => [
                    'expires_at' => $session->expires_at->toIso8601String(),
                    'last_activity' => $session->last_activity
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Profile fetch error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $sessionId ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $sessionId = $request->header('Authorization') ?? $request->header('X-Session-ID');
            if ($sessionId) {
                $sessionId = str_replace('Bearer ', '', $sessionId);
                
                DB::beginTransaction();
                try {
                    $session = Session::where('id', $sessionId)->first();
                    if ($session) {
                        $session->expires_at = Carbon::now();
                        $session->save();
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => $sessionId ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function touristLogin(Request $request)
    {
        try {
            Log::info('Tourist login attempt', ['email' => $request->email]);

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find tourist
            $tourist = Tourist::where('email', $request->email)->first();
            
            if (!$tourist || !Hash::check($request->password, $tourist->password)) {
                Log::warning('Invalid tourist login attempt', ['email' => $request->email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            DB::beginTransaction();
            try {
                // Invalidate old sessions
                Session::where('tourist_id', $tourist->id)
                    ->update(['expires_at' => Carbon::now()]);

                // Create new session
                $session = new Session([
                    'tourist_id' => $tourist->id,
                    'token' => Str::random(60),
                    'expires_at' => Carbon::now()->addDays(30),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => Carbon::now()->timestamp
                ]);

                $session->save();
                DB::commit();

                // Hide sensitive data
                $tourist = $tourist->makeHidden(['password']);

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => [
                        'tourist' => $tourist,
                        'token' => $session->token,
                        'expires_at' => $session->expires_at->toIso8601String()
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Tourist login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
