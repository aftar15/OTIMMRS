<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ActivityBooking extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'activity_id',
        'tourist_id',
        'number_of_participants',
        'scheduled_date',
        'status',
        'special_requests',
        'total_cost',
        'participant_details'
    ];

    protected $casts = [
        'id' => 'string',
        'activity_id' => 'string',
        'tourist_id' => 'string',
        'scheduled_date' => 'datetime',
        'total_cost' => 'decimal:2',
        'participant_details' => 'json',
        'number_of_participants' => 'integer'
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

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }
}
