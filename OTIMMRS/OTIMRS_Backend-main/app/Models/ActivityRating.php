<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'activity_id',
        'tourist_id',
        'rating',
        'comment'
    ];

    protected $casts = [
        'id' => 'string',
        'activity_id' => 'string',
        'tourist_id' => 'string',
        'rating' => 'float'
    ];

    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }
}
