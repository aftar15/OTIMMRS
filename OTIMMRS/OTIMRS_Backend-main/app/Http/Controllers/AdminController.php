<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        try {
            $admins = Admin::select('id', 'name', 'email', 'username', 'profile_picture', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $admins
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch admins', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admins',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins',
                'username' => 'required|string|max:255|unique:admins',
                'password' => 'required|string|min:6',
                'profile_picture' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $admin = new Admin([
                'id' => (string) Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'profile_picture' => $request->profile_picture
            ]);

            $admin->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully',
                'data' => $admin->makeHidden(['password'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $admin->makeHidden(['password'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $admin = Admin::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:admins,email,' . $id,
                'username' => 'required|string|max:255|unique:admins,username,' . $id,
                'password' => 'nullable|string|min:6',
                'profile_picture' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $admin->name = $request->name;
            $admin->email = $request->email;
            $admin->username = $request->username;
            if ($request->password) {
                $admin->password = Hash::make($request->password);
            }
            if ($request->has('profile_picture')) {
                $admin->profile_picture = $request->profile_picture;
            }

            $admin->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
                'data' => $admin->makeHidden(['password'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);

            DB::beginTransaction();
            $admin->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Admin deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 