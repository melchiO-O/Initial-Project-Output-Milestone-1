<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'license_number',
        'license_expiry',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'license_expiry'    => 'date',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasLicense(): bool
    {
        return !empty($this->license_number) && !empty($this->license_expiry);
    }

    public function rental()
    {
        // One active rental per user at a time
        return $this->hasOne(Rental::class)->whereIn('status', ['pending', 'active']);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }
}