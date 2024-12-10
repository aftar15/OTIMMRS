<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $fillable = [
        'id',
        'name',
        'email',
        'profile_picture',
        'username',
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function getActiveSession()
    {
        return $this->sessions()
            ->where('expires_at', '>', now())
            ->orderBy('last_activity', 'desc')
            ->first();
    }

    public function invalidateAllSessions()
    {
        return $this->sessions()
            ->update(['expires_at' => now()]);
    }
}
