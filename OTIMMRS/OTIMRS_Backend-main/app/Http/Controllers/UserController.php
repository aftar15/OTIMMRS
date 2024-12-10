<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Admin;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //

    public function login(Request $request)
    {
        try {
            Log::info('Login attempt', ['username' => $request->get('username')]);

            if (empty($request->get('username'))) {
                return response()->json(['error' => 'Username is required'], 422);
            }

            if (empty($request->get('password'))) {
                return response()->json(['error' => 'Password is required'], 422);
            }

            $username = $request->get('username');
            $password = $request->get('password');

            $admin = Admin::where('username', $username)->first();
            
            if (empty($admin)) {
                Log::info('User not found', ['username' => $username]);
                return response()->json(['error' => 'User not found'], 404);
            }

            if (!Hash::check($password, $admin->password)) {
                Log::info('Invalid password', ['username' => $username]);
                return response()->json(['error' => 'Invalid password'], 401);
            }
            
            // Set session expiry to 24 hours from now
            $expiresAt = Carbon::now()->addHours(24);

            // Create or update session
            $session = Session::updateOrCreate(
                ['admin_id' => $admin->id],
                ['expires_at' => $expiresAt]
            );

            // Prepare response data
            $responseData = [
                'id' => $admin->id,
                'username' => $admin->username,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'session_id' => $session->id,
                'expires_at' => $expiresAt->toIso8601String()
            ];

            Log::info('Login successful', ['username' => $username, 'admin_id' => $admin->id]);

            return response()->json($responseData, 200);
        } catch (\Exception $e) {
            Log::error('Login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'An error occurred during login',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function register()
    {
        if (empty(request()->get('username'))) return ['error' => 'Empty username'];

        $first_name = request()->get('first_name');
        $middle_name = request()->get('middle_name');
        $last_name = request()->get('last_name');
        $profile_picture = request()->get('profile_picture');
        $username = request()->get('username');
        $password = request()->get('password');
        
        Admin::firstOrCreate([
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'profile_picture' => $profile_picture,
            'username' => $username,
            'password' => Hash::make($password),
        ]);

        return ['message' => 'User created'];
    }

    public function registerMobile()
    {
        if (empty(request()->get('name'))) return ['error' => 'Empty name'];
        if (empty(request()->get('email'))) return ['error' => 'Empty email'];
        if (empty(request()->get('password'))) return ['error' => 'Empty password'];
        if (empty(request()->get('gender'))) return ['error' => 'Empty gender'];
        if (empty(request()->get('address'))) return ['error' => 'Empty address'];
        if (empty(request()->get('nationality'))) return ['error' => 'Empty nationality'];
        if (empty(request()->get('hobbies'))) return ['error' => 'Empty hobbies'];

        $name = request()->get('name');
        $email = request()->get('email');
        $password = request()->get('password');
        $gender = request()->get('gender');
        $address = request()->get('address');
        $nationality = request()->get('nationality');
        $hobbies = request()->get('hobbies');
        
        User::firstOrCreate([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'gender' => $gender,
            'address' => $address,
            'nationality' => $nationality,
            'hobbies' => $hobbies
        ]);

        return ['message' => 'User created'];
    }

    public function get() {

        $users = Admin::orderBy('id', 'desc')->get();

        return ['success' => 1, 'data' => $users];
    }

    public function getById($id) {

        $user = Admin::find($id);

        if ($user == null) return ['error' => 'User not found'];

        return ['success' => 1, 'data' => $user];
    }

    public function getMobileUserById($id) {

        $user = User::find($id);

        if ($user == null) return ['error' => 'User not found'];

        return ['success' => 1, 'data' => $user];
    }

    public function editById($id)
    {
        if (empty(request()->get('first_name')) || empty(request()->get('last_name')) || empty(request()->get('username'))) {
            return ['error' => 'Empty field'];
        }

        $admin = Admin::find($id);

        if ($admin == null) return ['error' => 'User not found'];

        $admin->first_name = request()->get('first_name');
        $admin->middle_name = request()->get('middle_name');
        $admin->last_name = request()->get('last_name');
        $admin->profile_picture = request()->get('profile_picture');
        $admin->username = request()->get('username');
        if (request()->get('password') != null) $admin->password = Hash::make(request()->get('password'));
        $admin->save();

        return ['message' => 'User edited'];
    }


    public function deleteById($id)
    {
        $user = Admin::find($id);

        if ($user == null) return ['error' => 'User not found'];

        // Delete sessions with the admin_id
        Session::where('admin_id', $id)->delete();

        $user->delete();

        return ['message' => 'User deleted'];
    }


    public function logoutSessions($id)
    {
        $user = Admin::find($id);

        if ($user == null) return ['error' => 'User not found'];

        // Delete sessions with the admin_id
        Session::where('admin_id', $id)->delete();

        return ['message' => 'Sessions logged out'];
    }

    public function logout()
    {
        // Check the request header for authorization token
        if (!request()->hasHeader('Authorization')) return ['error' => 'Authorization ID empty'];
        $id = request()->header('Authorization');

        // Check if session exists
        $session = Session::where('id', $id)->delete();

        return ['message' => 'Logged Out'];
    }
}
