<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccommodationRating extends Model
{
    use HasFactory;

    protected $table = 'accommodation_ratings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'accommodation_id',
        'tourist_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'rating' => 'decimal:1'
    ];

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) \Illuminate\Support\Str::uuid();
        });
    }
}
