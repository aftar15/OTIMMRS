<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TouristController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AttractionController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\ArrivalController;
use App\Http\Controllers\TouristAttractionController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Test route to verify API is working
Route::get('/test', function() {
    return response()->json(['message' => 'API is working']);
});

// Mobile App Routes
Route::prefix('mobile')->group(function () {
    // Public routes
    Route::post('/register', [TouristController::class, 'addFromMobile']);
    Route::post('/login', [AuthController::class, 'touristLogin']);
    
    // Protected routes
    Route::middleware(['tourist.auth'])->group(function () {
        // Profile
        Route::get('/profile', [TouristController::class, 'profile']);
        
        // Activities
        Route::prefix('activities')->group(function () {
            Route::get('/get/popular', [ActivityController::class, 'getPopular']);
            Route::get('/get/recommended', [ActivityController::class, 'getRecommended']);
            Route::get('/{id}', [ActivityController::class, 'getById']);
            Route::get('/{id}/rating', [ActivityController::class, 'getRating']);
            Route::post('/{id}/rating', [ActivityController::class, 'addRating']);
            Route::post('/{id}/comment', [ActivityController::class, 'addComment']);
        });
        
        // Attractions
        Route::prefix('attractions')->group(function () {
            Route::get('/', [AttractionController::class, 'getAll']);
            Route::get('/get/popular', [AttractionController::class, 'getPopular']);
            Route::get('/get/recommended', [AttractionController::class, 'getRecommended']);
            Route::get('/{id}', [AttractionController::class, 'getById']);
            Route::get('/{id}/rating', [AttractionController::class, 'getRating']);
            Route::post('/{id}/rating', [AttractionController::class, 'addRating']);
            Route::post('/{id}/comment', [AttractionController::class, 'addComment']);
        });
        
        // Accommodations routes
        Route::prefix('accommodations')->group(function () {
            Route::get('/', [AccommodationController::class, 'getAll']);
            Route::get('/recommended', [AccommodationController::class, 'getRecommended']);
            Route::get('/{id}', [AccommodationController::class, 'getById']);
            
            // Protected accommodation routes
            Route::middleware(['tourist.auth'])->group(function () {
                Route::post('/{id}/rating', [AccommodationController::class, 'addRating']);
                Route::get('/{id}/rating', [AccommodationController::class, 'getRating']);
                Route::get('/{id}/comments', [AccommodationController::class, 'getComments']);
            });
        });
        
        // Protected tourist routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/recommendations', [RecommendationController::class, 'getRecommendations']);
        Route::post('/preferences', [TouristController::class, 'updatePreferences']);
        Route::get('/search', [SearchController::class, 'search']);
    });
});

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']); // Admin login
Route::post('/tourist/login', [AuthController::class, 'touristLogin']); // Tourist login
Route::post('/tourist/register', [TouristController::class, 'register']);

// Mobile specific routes (for backward compatibility)
Route::prefix('mobile')->group(function () {
    Route::post('/login', [AuthController::class, 'touristLogin']);
    Route::post('/register', [TouristController::class, 'addFromMobile']);
});

// Protected routes requiring tourist authentication
Route::middleware(['tourist.auth'])->group(function () {
    Route::get('/tourist/profile', [TouristController::class, 'profile']);
    Route::post('/tourist/logout', [AuthController::class, 'touristLogout']);
    // ... other protected tourist routes ...
});

// Protected routes requiring admin authentication
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // ... other admin routes ...
});

// Public admin routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/profile', [AuthController::class, 'profile']);
Route::middleware(['auth:sanctum', 'admin'])->get('/tourists/list', [TouristController::class, 'list']);
Route::get('/attractions', [AttractionController::class, 'getAllForAdmin']);
Route::get('/attractions/{id}', [AttractionController::class, 'show']);
Route::get('/accommodations', [AccommodationController::class, 'getAllForAdmin']);

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/tourists/list', [TouristController::class, 'list']);
    Route::get('/attractions', [AttractionController::class, 'getAllForAdmin']);
    Route::get('/attractions/{id}', [AttractionController::class, 'show']);
    
    // Comments Management
    Route::get('/comments', [CommentController::class, 'index']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    
    // Other admin routes...
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::middleware(['check_session_auth'])->group(function () {
        // Admin management routes
        Route::prefix('admins')->group(function () {
            Route::get('/', [AdminController::class, 'index']);
            Route::post('/', [AdminController::class, 'store']);
            Route::get('/{id}', [AdminController::class, 'show']);
            Route::put('/{id}', [AdminController::class, 'update']);
            Route::delete('/{id}', [AdminController::class, 'destroy']);
        });

        // Comments routes
        Route::prefix('comments')->group(function () {
            Route::get('/', [CommentController::class, 'index']);
            Route::get('/{id}', [CommentController::class, 'show']);
            Route::delete('/{id}', [CommentController::class, 'destroy']);
        });

        // Tourist routes
        Route::get('/tourists/{id}', [TouristController::class, 'person']);
        
        // Attraction routes
        Route::prefix('attractions')->group(function () {
            Route::get('/', [AttractionController::class, 'getAllForAdmin']);
            Route::post('/', [AttractionController::class, 'store']);
            Route::get('/{id}', [AttractionController::class, 'show']);
            Route::put('/{id}', [AttractionController::class, 'update']);
            Route::delete('/{id}', [AttractionController::class, 'destroy']);
            Route::get('/{id}/comments', [AttractionController::class, 'getComments']);
            Route::post('/{id}/image', [AttractionController::class, 'uploadImage']);
        });
        
        // Accommodation routes
        Route::prefix('accommodations')->group(function () {
            Route::get('/', [AccommodationController::class, 'getAllForAdmin']);
            Route::post('/', [AccommodationController::class, 'store']);
            Route::get('/{id}', [AccommodationController::class, 'show']);
            Route::put('/{id}', [AccommodationController::class, 'update']);
            Route::delete('/{id}', [AccommodationController::class, 'destroy']);
            Route::get('/{id}/comments', [AccommodationController::class, 'getComments']);
            Route::post('/{id}/image', [AccommodationController::class, 'uploadImage']);
        });

        // Activity routes
        Route::prefix('activities')->group(function () {
            Route::get('/', [ActivityController::class, 'getAllForAdmin']);
            Route::post('/', [ActivityController::class, 'store']);
            Route::get('/{id}', [ActivityController::class, 'show']);
            Route::put('/{id}', [ActivityController::class, 'update']);
            Route::delete('/{id}', [ActivityController::class, 'destroy']);
        });

        // Arrival routes
        Route::prefix('arrivals')->group(function () {
            Route::get('/', [ArrivalController::class, 'getAllForAdmin']);
            Route::post('/', [ArrivalController::class, 'store']);
            Route::get('/{id}', [ArrivalController::class, 'show']);
            Route::put('/{id}', [ArrivalController::class, 'update']);
            Route::delete('/{id}', [ArrivalController::class, 'destroy']);
        });
        
        // Report routes
        Route::get('/reports/overview', [ReportController::class, 'getOverview']);
        Route::get('/reports/tourists', [ReportController::class, 'getTouristReport']);
        Route::get('/reports/attractions', [ReportController::class, 'getAttractionReport']);
    });

    // Comments routes
    Route::get('/comments', [App\Http\Controllers\Admin\CommentController::class, 'index']);
    Route::get('/comments/{id}', [App\Http\Controllers\Admin\CommentController::class, 'show']);
    Route::delete('/comments/{id}', [App\Http\Controllers\Admin\CommentController::class, 'destroy']);
});

// Tourist routes
Route::middleware(['auth.session'])->group(function () {
    Route::get('/tourists', [TouristController::class, 'index']);
    Route::post('/tourists', [TouristController::class, 'store']);
    Route::get('/tourists/{id}', [TouristController::class, 'show']);
    Route::put('/tourists/{id}', [TouristController::class, 'update']);
    Route::delete('/tourists/{id}', [TouristController::class, 'destroy']);
});

// Mobile routes
Route::prefix('mobile')->middleware(['tourist.auth'])->group(function () {
    // Activities
    Route::prefix('activities')->group(function () {
        Route::get('/get/popular', [ActivityController::class, 'getPopular']);
        Route::get('/get/recommended', [ActivityController::class, 'getRecommended']);
        Route::post('/{id}/rating', [ActivityController::class, 'addRating']);
        Route::get('/{id}/rating', [ActivityController::class, 'getRating']);
        Route::post('/{id}/comment', [ActivityController::class, 'addComment']);
    });

    // Attractions
    Route::prefix('attractions')->group(function () {
        Route::get('/get/popular', [AttractionController::class, 'getPopular']);
        Route::get('/get/recommended', [AttractionController::class, 'getRecommended']);
        Route::get('/{id}/rating', [AttractionController::class, 'getRating']);
        Route::post('/{id}/rating', [AttractionController::class, 'addRating']);
        Route::post('/comment', [AttractionController::class, 'addComment']);
    });

    // Accommodations
    Route::prefix('accommodations')->group(function () {
        Route::get('/get/popular', [AccommodationController::class, 'getPopular']);
        Route::get('/get/recommended', [AccommodationController::class, 'getRecommended']);
        Route::post('/{id}/rating', [AccommodationController::class, 'addRating']);
        Route::get('/{id}/rating', [AccommodationController::class, 'getRating']);
        Route::post('/comment', [AccommodationController::class, 'addComment']);
    });
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Comments routes
    Route::prefix('comments')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CommentController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Admin\CommentController::class, 'show']);
        Route::delete('/{id}', [App\Http\Controllers\Admin\CommentController::class, 'destroy']);
    });

    // ... rest of admin routes ...
});

// Admin auth routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'admin']);
});

// Protected admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Comments routes
    Route::prefix('comments')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CommentController::class, 'index']);
        Route::get('/{id}', [App\Http\Controllers\Admin\CommentController::class, 'show']);
        Route::delete('/{id}', [App\Http\Controllers\Admin\CommentController::class, 'destroy']);
    });

    // Other admin routes...
});

Route::get('/tourists/{id}', [TouristController::class, 'show']);
