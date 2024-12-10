<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attraction extends Model
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
        'views',
        'rating',
        'latitude',
        'longitude',
        'contact_info',
        'opening_hours',
        'admission_fee',
        'tags',
        'contact_email',
        'map_source',
        'contact_phone'
    ];

    protected $casts = [
        'views' => 'integer',
        'rating' => 'decimal:1',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'contact_info' => 'json',
        'opening_hours' => 'json',
        'admission_fee' => 'decimal:2',
        'tags' => 'json'
    ];

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
            $model->rating = 0;
        });
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
