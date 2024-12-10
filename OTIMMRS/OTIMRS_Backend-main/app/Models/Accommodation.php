<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Accommodation extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description',
        'location',
        'category',
        'image_url',
        'price_per_night',
        'capacity',
        'amenities',
        'contact_info',
        'views'
    ];

    protected $casts = [
        'amenities' => 'json',
        'contact_info' => 'json',
        'views' => 'integer',
        'price_per_night' => 'decimal:2',
        'capacity' => 'integer'
    ];

    public function ratings()
    {
        return $this->hasMany(AccommodationRating::class);
    }

    public function arrivals()
    {
        return $this->hasMany(Arrival::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
            $model->views = 0;
        });
    }
}
