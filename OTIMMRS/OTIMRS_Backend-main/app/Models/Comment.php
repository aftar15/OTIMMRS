<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'tourist_id',
        'commentable_id',
        'commentable_type',
        'comment',
        'transportation',
        'transportation_fee',
        'services',
        'road_problems',
        'price_increase',
        'others'
    ];

    protected $casts = [
        'transportation_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'commentable_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
