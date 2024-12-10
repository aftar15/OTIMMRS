<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Models\Tourist;
use Carbon\Carbon;

class Session extends Model
{
    use HasFactory;

    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'token',
        'expires_at',
        'last_activity',
        'admin_id', 
        'tourist_id', 
        'payload',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'payload' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
            if (!$model->expires_at) {
                $model->expires_at = Carbon::now()->addDays(1);
            }
            if (!$model->last_activity) {
                $model->last_activity = time();
            }
            if (!$model->payload) {
                $model->payload = [];
            }
        });

        static::updating(function ($model) {
            $model->last_activity = time();
        });
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function tourist()
    {
        return $this->belongsTo(Tourist::class);
    }

    public function user()
    {
        return $this->morphTo('user');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function updateLastActivity()
    {
        $this->last_activity = time();
        $this->save();
    }

    public function extendExpiry($days = 1)
    {
        $this->expires_at = Carbon::now()->addDays($days);
        $this->save();
    }
}
