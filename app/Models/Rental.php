<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rental extends Model
{
    use HasFactory;

    // Overdue fee constant
    const OVERDUE_FEE_PER_HOUR = 200;

    protected $table = 'rentals';

    protected $fillable = [
        'user_id',
        'car_id',
        'license_number',
        'license_expiry',
        'pickup_datetime',
        'return_datetime',
        'actual_return_datetime',
        'duration_hours',
        'price_per_day',
        'total_days',
        'total_price',
        'status',
        'pickup_location',
        'notes',
    ];

    protected $casts = [
        'pickup_datetime'        => 'datetime',
        'return_datetime'        => 'datetime',
        'actual_return_datetime' => 'datetime',
        'license_expiry'         => 'date',
        'duration_hours'         => 'integer',
        'total_price'            => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ── Computed: Time remaining ───────────────────────────────

    public function getTimeRemainingAttribute()
    {
        if ($this->status === 'returned' || $this->status === 'completed') {
            return 'Rental completed';
        }
        if ($this->status === 'cancelled') {
            return 'Rental cancelled';
        }

        $now        = Carbon::now();
        $returnTime = Carbon::parse($this->return_datetime);

        if ($now->gt($returnTime)) {
            $hoursOverdue = $now->diffInHours($returnTime);
            return "Overdue by {$hoursOverdue} hours";
        }

        $hoursLeft   = $now->diffInHours($returnTime);
        $minutesLeft = $now->diffInMinutes($returnTime) % 60;

        if ($hoursLeft > 24) {
            $daysLeft       = floor($hoursLeft / 24);
            $remainingHours = $hoursLeft % 24;
            return "{$daysLeft}d {$remainingHours}h remaining";
        } elseif ($hoursLeft > 0) {
            return "{$hoursLeft}h {$minutesLeft}m remaining";
        } else {
            return "Less than an hour remaining";
        }
    }

    // ── Computed: Progress percentage ─────────────────────────

    public function getProgressPercentageAttribute()
    {
        $start = Carbon::parse($this->pickup_datetime);
        $end   = Carbon::parse($this->return_datetime);
        $now   = Carbon::now();

        if ($now->lt($start)) return 0;
        if ($now->gt($end))   return 100;

        $total   = $start->diffInMinutes($end);
        $elapsed = $start->diffInMinutes($now);

        return min(100, round(($elapsed / $total) * 100));
    }

    // ── Calculate overdue fee ──────────────────────────────────

    public function calculateOverdueFee()
    {
        $now = Carbon::now();

        $checkTime = $this->actual_return_datetime
            ? Carbon::parse($this->actual_return_datetime)
            : $now;

        $returnTime = Carbon::parse($this->return_datetime);

        if ($checkTime->lte($returnTime)) {
            return 0;
        }

        $hoursOverdue = ceil($returnTime->diffInHours($checkTime, true));
        return $hoursOverdue * self::OVERDUE_FEE_PER_HOUR;
    }

    // ── Get formatted overdue time ─────────────────────────────

    public function getOverdueTimeAttribute()
    {
        if ($this->status !== 'active') {
            return null;
        }

        $now        = Carbon::now();
        $returnTime = Carbon::parse($this->return_datetime);

        if ($now->lte($returnTime)) {
            return null;
        }

        $hoursOverdue       = ceil($returnTime->diffInHours($now, true));
        $daysOverdue        = floor($hoursOverdue / 24);
        $remainingHours     = $hoursOverdue % 24;

        if ($daysOverdue > 0) {
            return "{$daysOverdue} day(s) and {$remainingHours} hour(s)";
        }

        return "{$hoursOverdue} hour(s)";
    }

    // ── Get formatted overdue time for returned rentals ────────

    public function getReturnedOverdueTimeAttribute()
    {
        if (!$this->actual_return_datetime) {
            return null;
        }

        $actualReturn = Carbon::parse($this->actual_return_datetime);
        $returnTime   = Carbon::parse($this->return_datetime);

        if ($actualReturn->lte($returnTime)) {
            return null;
        }

        $hoursOverdue   = ceil($returnTime->diffInHours($actualReturn, true));
        $daysOverdue    = floor($hoursOverdue / 24);
        $remainingHours = $hoursOverdue % 24;

        if ($daysOverdue > 0) {
            return "{$daysOverdue} day(s) and {$remainingHours} hour(s)";
        }

        return "{$hoursOverdue} hour(s)";
    }

    // ── Calculate actual cost (with overdue handling) ──────────

    public function calculateActualCost()
    {
        if (!$this->actual_return_datetime) {
            return null;
        }

        $actualReturn    = Carbon::parse($this->actual_return_datetime);
        $pickup          = Carbon::parse($this->pickup_datetime);
        $scheduledReturn = Carbon::parse($this->return_datetime);

        $originalHours = $this->duration_hours;
        $actualHours   = ceil($pickup->diffInHours($actualReturn));

        // Hourly rate from remaining balance (down payment already paid)
        $hourlyRate = $this->price_per_day / 24;

        // Overdue calculation
        $overdueHours     = 0;
        $formattedOverdue = null;
        if ($actualReturn->gt($scheduledReturn)) {
            $overdueHours     = ceil($scheduledReturn->diffInHours($actualReturn, true));
            $overdueDays      = floor($overdueHours / 24);
            $overdueRemaining = $overdueHours % 24;
            $formattedOverdue = $overdueDays > 0
                ? "{$overdueDays} day(s) and {$overdueRemaining} hour(s)"
                : "{$overdueHours} hour(s)";
        }

        $overdueFee      = $overdueHours * self::OVERDUE_FEE_PER_HOUR;
        $downPaymentKept = $this->payment->down_payment; // ← CHANGED to use payment relationship

        if ($actualHours >= $originalHours) {
            // On-time or overdue: customer pays full remaining balance
            $amountPaidFromRemaining = (float) $this->payment->remaining_balance; // ← CHANGED
        } else {
            // Early return: only pay for actual hours minus down payment portion
            $remainingBalancePerHour = $this->payment->remaining_balance / $originalHours; // ← CHANGED
            $amountPaidFromRemaining = $remainingBalancePerHour * $actualHours;
            $amountPaidFromRemaining = max(0, $amountPaidFromRemaining);
        }

        // Subtotal = what customer pays for rental time only
        $subTotal = $downPaymentKept + $amountPaidFromRemaining;

        // Grand total = subtotal + all fees
        $grandTotal = $subTotal + $overdueFee;

        $savings = 0;
        if ($overdueHours === 0 && $actualHours < $originalHours) {
            $savings = (float) $this->total_price - $subTotal;
        }

        return [
            'actual_hours'               => $actualHours,
            'original_hours'             => $originalHours,
            'overdue_hours'              => $overdueHours,
            'formatted_overdue'          => $formattedOverdue,
            'hourly_rate'                => $hourlyRate,
            'down_payment_kept'          => $downPaymentKept,
            'amount_paid_from_remaining' => $amountPaidFromRemaining,
            'overdue_fee'                => $overdueFee,
            'sub_total'                  => $subTotal,
            'grand_total'                => $grandTotal,
            'amount_due_on_return'       => $amountPaidFromRemaining + $overdueFee,
            'total_paid'                 => $grandTotal,
            'original_total'             => (float) $this->total_price,
            'remaining_balance_per_hour' => $this->payment->remaining_balance / $originalHours, // ← CHANGED
            'savings'                    => $savings,
        ];
    }

    // ── SUBTOTAL: down payment + amount paid from remaining (NO damage fee) ──
    public function getSubTotalAttribute(): float
    {
        if ($this->status === 'cancelled') {
            return 0;
        }

        if ($this->actual_return_datetime && $this->status === 'returned') {
            $actual = $this->calculateActualCost();
            if ($actual) {
                return round((float) $actual['sub_total'], 2);
            }
        }

        // Active/pending: full total_price
        return round((float) $this->total_price, 2);
    }

    // ── GRAND TOTAL: subtotal + damage fee + overdue fee ──
    public function getGrandTotalAttribute(): float
    {
        if ($this->status === 'cancelled') {
            return 0;
        }

        $damageFee = (float) ($this->payment->damage_fee ?? 0); // ← CHANGED

        if ($this->actual_return_datetime && $this->status === 'returned') {
            $actual = $this->calculateActualCost();
            if ($actual) {
                return round((float) $actual['grand_total'] + $damageFee, 2);
            }
        }

        // Active/pending
        $overdueFee = $this->calculateOverdueFee();
        return round((float) $this->total_price + $damageFee + $overdueFee, 2);
    }

    // ── DUE ON RETURN: what customer still owes at return time ──
    public function getDueOnReturnAttribute(): float
    {
        if ($this->status === 'cancelled') {
            return 0;
        }

        $damageFee = (float) ($this->payment->damage_fee ?? 0); // ← CHANGED

        if ($this->actual_return_datetime && $this->status === 'returned') {
            $actual = $this->calculateActualCost();
            if ($actual) {
                return round((float) $actual['amount_due_on_return'] + $damageFee, 2);
            }
        }

        $overdueFee = $this->calculateOverdueFee();
        return round((float) ($this->payment->remaining_balance ?? 0) + $damageFee + $overdueFee, 2); // ← CHANGED
    }
    
    // ── Computed: Formatted duration ──────────────────────────

    public function getFormattedDurationAttribute()
    {
        $hours    = $this->duration_hours;
        $duration = $hours . ' hour' . ($hours !== 1 ? 's' : '');

        if ($hours >= 24) {
            $days        = $hours / 24;
            $daysDisplay = is_int($days) ? $days : number_format($days, 1);
            $duration   .= ' (' . $daysDisplay . ' day' . ($daysDisplay != 1 ? 's' : '') . ')';
        }

        return $duration;
    }

    // ── Final amount paid (for returned rentals) ───────────────

    public function getFinalAmountPaidAttribute()
    {
        return $this->grand_total;
    }
}