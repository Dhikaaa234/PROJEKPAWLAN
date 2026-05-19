<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'name',
        'nim',
        'email',
        'password',
        'role_id',
        'nim', 
        'no_telepon',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELATION
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function logs()
    {
        return $this->hasMany(ReportLog::class);
    }

    public function reportLogs()
    {
        return $this->hasMany(ReportLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function getRoleNameAttribute(): string
    {
        return $this->role?->name ?? 'user';
    }
}