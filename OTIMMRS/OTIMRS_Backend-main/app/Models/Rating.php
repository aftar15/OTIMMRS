<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Rating extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'tourist_id',
        'attraction_id',
        'rating'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }

    public function attraction()
    {
        return $this->belongsTo(Attraction::class);
    }
}
