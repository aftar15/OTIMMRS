<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Arrival extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tourist_id',
        'arrival_date',
        'departure_date',
        'purpose_of_visit',
        'accommodation_id',
        'transportation_mode',
        'number_of_companions',
        'status',
        'notes',
        'contact_number',
        'emergency_contact',
        'emergency_contact_number'
    ];

    protected $casts = [
        'id' => 'string',
        'tourist_id' => 'string',
        'accommodation_id' => 'string',
        'arrival_date' => 'datetime',
        'departure_date' => 'datetime',
        'number_of_companions' => 'integer'
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

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
} 