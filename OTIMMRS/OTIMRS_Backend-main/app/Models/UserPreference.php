<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'interests',
        'preferred_categories',
        'preferred_locations',
        'preferred_price_range_min',
        'preferred_price_range_max',
        'activity_history',
        'search_history',
        'click_history'
    ];

    protected $casts = [
        'interests' => 'array',
        'preferred_categories' => 'array',
        'preferred_locations' => 'array',
        'activity_history' => 'array',
        'search_history' => 'array',
        'click_history' => 'array',
        'preferred_price_range_min' => 'decimal:2',
        'preferred_price_range_max' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updateSearchHistory(string $query)
    {
        $history = $this->search_history ?? [];
        array_unshift($history, [
            'query' => $query,
            'timestamp' => now()->toIso8601String()
        ]);
        $this->search_history = array_slice($history, 0, 50); // Keep last 50 searches
        $this->save();
    }

    public function updateClickHistory($itemId, $itemType)
    {
        $history = $this->click_history ?? [];
        array_unshift($history, [
            'item_id' => $itemId,
            'item_type' => $itemType,
            'timestamp' => now()->toIso8601String()
        ]);
        $this->click_history = array_slice($history, 0, 100); // Keep last 100 clicks
        $this->save();
    }

    public function updateActivityHistory($activityId, $action)
    {
        $history = $this->activity_history ?? [];
        array_unshift($history, [
            'activity_id' => $activityId,
            'action' => $action,
            'timestamp' => now()->toIso8601String()
        ]);
        $this->activity_history = array_slice($history, 0, 100);
        $this->save();
    }
}
