<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tourist;
use App\Models\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AuthService
{
    public function authenticateUser($credentials, $userType = 'admin')
    {
        $model = $userType === 'admin' ? User::class : Tourist::class;
        $user = $model::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        // Create session token
        $token = Str::random(60);
        $expiresAt = Carbon::now()->addHours(24);

        // Store session
        Session::updateOrCreate(
            ['user_id' => $user->id, 'user_type' => $userType],
            [
                'token' => $token,
                'expires_at' => $expiresAt,
                'last_activity' => Carbon::now()
            ]
        );

        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => $expiresAt
        ];
    }

    public function validateSession($token)
    {
        $session = Session::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$session) {
            return null;
        }

        // Update last activity
        $session->update(['last_activity' => Carbon::now()]);

        return $session->user_type === 'admin' 
            ? User::find($session->user_id)
            : Tourist::find($session->user_id);
    }

    public function logout($token)
    {
        return Session::where('token', $token)->delete();
    }
}
