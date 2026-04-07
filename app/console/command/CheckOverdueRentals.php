<?php
// app/Console/Commands/CheckOverdueRentals.php

namespace App\Console\Commands;

use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueRentals extends Command
{
    protected $signature = 'rentals:check-overdue';
    protected $description = 'Check for overdue rentals and update status';

    public function handle()
    {
        $overdueRentals = Rental::where('status', 'active')
            ->where('return_datetime', '<', Carbon::now())
            ->get();

        foreach ($overdueRentals as $rental) {
            $rental->update(['status' => 'overdue']);
            $this->info("Rental #{$rental->id} marked as overdue");
        }

        $this->info("Checked {$overdueRentals->count()} overdue rentals");
    }
}