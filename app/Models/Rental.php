<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'car_id',
        'pickup_datetime',
        'return_datetime',
        'duration_hours',
        'total_price',
        'status',
        'pickup_location',
        'return_location',
        'notes'
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'duration_hours' => 'integer', // Add this line
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function getTimeRemainingAttribute()
    {
        if ($this->status === 'completed') {
            return 'Rental completed';
        }

        if ($this->status === 'cancelled') {
            return 'Rental cancelled';
        }

        $now = Carbon::now();
        $returnTime = Carbon::parse($this->return_datetime);

        if ($now->gt($returnTime)) {
            $hoursOverdue = $now->diffInHours($returnTime);
            return "Overdue by {$hoursOverdue} hours";
        }

        $hoursLeft = $now->diffInHours($returnTime);
        $minutesLeft = $now->diffInMinutes($returnTime) % 60;
        
        if ($hoursLeft > 24) {
            $daysLeft = floor($hoursLeft / 24);
            $remainingHours = $hoursLeft % 24;
            return "{$daysLeft}d {$remainingHours}h remaining";
        } elseif ($hoursLeft > 0) {
            return "{$hoursLeft}h {$minutesLeft}m remaining";
        } else {
            return "Less than an hour remaining";
        }
    }

    public function getProgressPercentageAttribute()
    {
        $start = Carbon::parse($this->pickup_datetime);
        $end = Carbon::parse($this->return_datetime);
        $now = Carbon::now();
        
        if ($now->lt($start)) {
            return 0;
        }
        
        if ($now->gt($end)) {
            return 100;
        }
        
        $total = $start->diffInMinutes($end);
        $elapsed = $start->diffInMinutes($now);
        
        return min(100, round(($elapsed / $total) * 100));
    }
}