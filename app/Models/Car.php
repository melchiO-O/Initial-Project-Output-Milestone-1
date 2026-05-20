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
        'plate_number',
        'year',
        'price_per_day',
        'seat_capacity',
        'status',
        'description',
        'image_path',
        'image_url',
    ];

    /**
     * Returns the correct image src to display.
     * Priority: uploaded file > external URL > null (show placeholder)
     */
    public function getImageSrc(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        if ($this->image_url) {
            return $this->image_url;
        }
        return null;
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function isAvailableForDates($pickupDatetime, $returnDatetime)
    {
        return !$this->rentals()
            ->where('status', 'active')
            ->where(function($query) use ($pickupDatetime, $returnDatetime) {
                $query->whereBetween('pickup_datetime', [$pickupDatetime, $returnDatetime])
                    ->orWhereBetween('return_datetime', [$pickupDatetime, $returnDatetime])
                    ->orWhere(function($q) use ($pickupDatetime, $returnDatetime) {
                        $q->where('pickup_datetime', '<=', $pickupDatetime)
                            ->where('return_datetime', '>=', $returnDatetime);
                    });
            })->exists();
    }

    public function getCurrentRental()
    {
        return $this->rentals()
            ->where('status', 'active')
            ->where('pickup_datetime', '<=', now())
            ->where('return_datetime', '>=', now())
            ->first();
    }

} 