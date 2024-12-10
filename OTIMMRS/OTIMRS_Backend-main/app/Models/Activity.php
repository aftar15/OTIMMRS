<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Activity extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'location',
        'category',
        'tags',
        'image_url',
        'rating',
        'price',
        'duration',
        'difficulty',
        'included_items',
        'start_time',
        'end_time',
        'schedule_type',
        'recurring_pattern',
        'cost',
        'capacity',
        'min_participants',
        'is_active',
        'requires_booking',
        'booking_deadline_hours',
        'latitude',
        'longitude',
        'map_source',
        'thumbnail_url',
        'contact_number',
        'contact_phone'
    ];

    protected $casts = [
        'id' => 'string',
        'tags' => 'array',
        'included_items' => 'array',
        'recurring_pattern' => 'array',
        'rating' => 'decimal:2',
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'capacity' => 'integer',
        'min_participants' => 'integer',
        'is_active' => 'boolean',
        'requires_booking' => 'boolean',
        'booking_deadline_hours' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function bookings()
    {
        return $this->hasMany(ActivityBooking::class);
    }
}
