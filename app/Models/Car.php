<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'seat_capacity',
        'plate_number',
        'year',
        'price_per_day',
        'status',
        'description',
        'image_path',
        'image_url',
    ];

    // Add this relationship
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    // Add this method to get current rental
    public function getCurrentRental()
    {
        return $this->rentals()
            ->where('status', 'active')
            ->where('pickup_datetime', '<=', now())
            ->where('return_datetime', '>=', now())
            ->first();
    }

    public function getImageSrc()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return $this->image_url ?? asset('images/default-car.jpg');
    }
}