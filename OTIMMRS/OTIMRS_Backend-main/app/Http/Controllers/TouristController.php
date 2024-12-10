<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Tourist;
use App\Models\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TouristController extends Controller
{
    private function processHobbies($hobbies)
    {
        $hobbyCategories = [
            'beach' => ['beach', 'water_sports', 'swimming'],
            'nature' => ['nature', 'hiking', 'adventure'],
            'culture' => ['culture', 'history', 'arts'],
            'food' => ['food', 'culinary', 'local_cuisine'],
            'shopping' => ['shopping', 'entertainment']
        ];

        $processedHobbies = [];
        $hobbyList = is_string($hobbies) ? json_decode($hobbies, true) : $hobbies;

        if (!is_array($hobbyList)) {
            return json_encode([
                ['name' => 'general', 'categories' => ['beach', 'nature', 'culture']]
            ]);
        }

        foreach ($hobbyList as $hobby) {
            $categories = [];
            foreach ($hobbyCategories as $mainCategory => $subCategories) {
                if (isset($hobby['name']) && 
                    (stripos($hobby['name'], $mainCategory) !== false || 
                     in_array(strtolower($hobby['name']), $subCategories))) {
                    $categories = array_merge($categories, $subCategories);
                }
            }
            
            if (empty($categories)) {
                $categories = ['beach', 'nature', 'culture']; // Default categories
            }

            $processedHobbies[] = [
                'name' => $hobby['name'] ?? 'general',
                'categories' => array_unique($categories)
            ];
        }

        return json_encode($processedHobbies);
    }

    public function add(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:tourists',
                'password' => 'required|string|min:8',
                'gender' => 'required|string|in:Male,Female,Other',
                'nationality' => 'required|string|max:100',
                'address' => 'required|string|max:255',
                'hobbies' => 'nullable|string',
                'accommodation_name' => 'nullable|string|max:255',
                'accommodation_location' => 'nullable|string|max:255',
                'accommodation_days' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Process hobbies if provided
            $hobbies = $request->hobbies ? $this->processHobbies($request->hobbies) : null;

            // Create tourist
            $tourist = Tourist::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'address' => $request->address,
                'hobbies' => $hobbies,
                'accommodation_name' => $request->accommodation_name,
                'accommodation_location' => $request->accommodation_location,
                'accommodation_days' => $request->accommodation_days
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tourist registered successfully',
                'data' => $tourist
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Tourist registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register tourist: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addFromMobile(Request $request)
    {
        try {
            // Custom validation messages
            $messages = [
                'email.unique' => 'This email is already registered. Please use a different email or try logging in.',
                'password.min' => 'Password must be at least 8 characters long.',
                'hobbies.required' => 'Please provide at least one hobby or interest.',
            ];

            // Validate the request
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|unique:tourists,email',
                'password' => 'required|string|min:8',
                'gender' => 'required|string|in:Male,Female,Other',
                'address' => 'required|string',
                'nationality' => 'required|string',
                'hobbies' => 'nullable',
                'accommodation_name' => 'nullable|string',
                'accommodation_location' => 'nullable|string',
                'accommodation_days' => 'nullable|integer'
            ], $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            
            // Process hobbies to ensure proper format
            $hobbies = $this->processHobbies($validatedData['hobbies'] ?? []);

            // Create the tourist
            $tourist = Tourist::create([
                'full_name' => $validatedData['full_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'gender' => $validatedData['gender'],
                'address' => $validatedData['address'],
                'nationality' => $validatedData['nationality'],
                'hobbies' => $hobbies,
                'accommodation_name' => $validatedData['accommodation_name'] ?? null,
                'accommodation_location' => $validatedData['accommodation_location'] ?? null,
                'accommodation_days' => $validatedData['accommodation_days'] ?? null
            ]);

            // Create a session for the new tourist
            $sessionId = Str::uuid()->toString();
            $session = Session::create([
                'id' => $sessionId,
                'tourist_id' => $tourist->id,
                'payload' => json_encode(['tourist_id' => $tourist->id]),
                'expires_at' => now()->addDays(7)
            ]);

            // Hide sensitive data
            $tourist = $tourist->makeHidden(['password']);

            return response()->json([
                'success' => 1,
                'message' => 'Registration successful',
                'data' => [
                    'tourist' => $tourist,
                    'token' => $sessionId
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get(Request $request)
    {
        try {
            // Check if admin exists in request (set by middleware)
            if (!$request->admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $tourists = Tourist::orderBy('created_at', 'desc')
                ->select('id', 'full_name', 'email', 'gender', 'nationality', 'address', 'created_at')
                ->get()
                ->map(function($tourist) {
                    $tourist->created_at = Carbon::parse($tourist->created_at)->format('Y-m-d H:i:s');
                    return $tourist;
                });

            return response()->json([
                'success' => true,
                'data' => $tourists
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching tourists: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tourists: ' . $e->getMessage()
            ], 500);
        }
    }

    public function person(Request $request, string $id)
    {
        try {
            // Check if admin exists in request (set by middleware)
            if (!$request->admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            $tourist = Tourist::where('id', $id)
                ->select('id', 'full_name', 'email', 'gender', 'nationality', 'address', 'hobbies', 
                        'accommodation_name', 'accommodation_location', 'accommodation_days', 'created_at')
                ->first();

            if (!$tourist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tourist not found'
                ], 404);
            }

            // Format the created_at date
            $tourist->created_at = Carbon::parse($tourist->created_at)->format('Y-m-d H:i:s');
            
            // Parse hobbies if it's a JSON string
            if (is_string($tourist->hobbies)) {
                $tourist->hobbies = json_decode($tourist->hobbies);
            }

            return response()->json([
                'success' => true,
                'data' => $tourist
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching tourist details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tourist details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getArrivals(Request $request) {

        $from = Carbon::parse($request->get('from'))->startOfDay();
        $to = Carbon::parse($request->get('to'))->endOfDay();

        $tourists = Tourist::whereBetween('created_at', [$from, $to])->orderBy('id', 'asc')->get();


        return ['success' => 1, 'data' => $tourists];
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();
            $tourist = Tourist::where('email', $validatedData['email'])->first();

            if (!$tourist || !Hash::check($validatedData['password'], $tourist->password)) {
                return response()->json([
                    'success' => 0,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Create a new session
            $sessionId = Str::uuid()->toString();
            $session = Session::create([
                'id' => $sessionId,
                'tourist_id' => $tourist->id,
                'payload' => json_encode(['tourist_id' => $tourist->id]),
                'expires_at' => now()->addDays(7)
            ]);

            // Hide sensitive data
            $tourist = $tourist->makeHidden(['password']);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'tourist' => $tourist,
                    'token' => $sessionId
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $session = $request->attributes->get('session');
            $session->delete();

            return response()->json([
                'success' => 1,
                'message' => 'Logout successful'
            ]);

        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => 0,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        try {
            // Ensure only authenticated admin can access this route
            if (!$request->admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 401);
            }

            // Fetch all tourists with eager loading and minimal processing
            $tourists = Tourist::all()->map(function($tourist) {
                // Ensure hobbies are always parsed
                $hobbies = $tourist->hobbies;
                if (is_string($hobbies)) {
                    try {
                        $hobbies = json_decode($hobbies, true);
                    } catch (\Exception $e) {
                        $hobbies = [];
                    }
                }

                return [
                    'id' => $tourist->id,
                    'full_name' => $tourist->full_name,
                    'email' => $tourist->email,
                    'gender' => $tourist->gender,
                    'nationality' => $tourist->nationality,
                    'address' => $tourist->address,
                    'hobbies' => $hobbies ?: [],
                    'profile_picture' => $tourist->profile_picture ?: '/user.jpg',
                    'accommodation_name' => $tourist->accommodation_name,
                    'accommodation_location' => $tourist->accommodation_location,
                    'accommodation_days' => $tourist->accommodation_days,
                    'created_at' => $tourist->created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $tourists
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching tourists: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tourists: ' . $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        try {
            $tourist = auth()->user();
            
            if (!$tourist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tourist not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tourist->id,
                    'full_name' => $tourist->full_name,
                    'email' => $tourist->email,
                    'gender' => $tourist->gender,
                    'address' => $tourist->address,
                    'nationality' => $tourist->nationality,
                    'hobbies' => json_decode($tourist->hobbies, true)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $tourists = Tourist::all();
            return response()->json([
                'success' => true,
                'data' => $tourists
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tourists',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:tourists',
                'password' => 'required|string|min:6',
                'gender' => 'required|string|in:Male,Female,Other',
                'address' => 'required|string',
                'nationality' => 'required|string',
                'hobbies' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourist = Tourist::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'gender' => $request->gender,
                'address' => $request->address,
                'nationality' => $request->nationality,
                'hobbies' => $request->hobbies
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tourist created successfully',
                'data' => $tourist
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating tourist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $tourist = Tourist::findOrFail($id);

            // Process hobbies if needed
            $hobbies = $tourist->hobbies;
            if (is_string($hobbies)) {
                try {
                    $hobbies = json_decode($hobbies, true);
                } catch (\Exception $e) {
                    $hobbies = [];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tourist->id,
                    'full_name' => $tourist->full_name,
                    'email' => $tourist->email,
                    'gender' => $tourist->gender,
                    'nationality' => $tourist->nationality,
                    'address' => $tourist->address,
                    'hobbies' => $hobbies ?: [],
                    'profile_picture' => $tourist->profile_picture ?: '/user.jpg',
                    'accommodation_name' => $tourist->accommodation_name,
                    'accommodation_location' => $tourist->accommodation_location,
                    'accommodation_days' => $tourist->accommodation_days,
                    'created_at' => $tourist->created_at
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching tourist details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Tourist not found or error occurred'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tourist = Tourist::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:tourists,email,' . $id,
                'gender' => 'required|string|in:Male,Female,Other',
                'address' => 'required|string',
                'nationality' => 'required|string',
                'hobbies' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourist->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tourist updated successfully',
                'data' => $tourist
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating tourist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tourist = Tourist::findOrFail($id);
            $tourist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tourist deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting tourist',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
