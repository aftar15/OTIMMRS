<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Tourist extends Authenticatable
{
    use Notifiable;

    protected $keyType = 'string';
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

    protected $fillable = [
        'id',
        'full_name',
        'email',
        'password',
        'gender',
        'nationality',
        'address',
        'hobbies',
        'accommodation_name',
        'accommodation_location',
        'accommodation_days'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'hobbies' => 'array'
    ];

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
