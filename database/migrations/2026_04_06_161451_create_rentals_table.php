<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');

            // License snapshot at time of rental
            $table->string('license_number');
            $table->date('license_expiry');

            // Dates
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime');
            $table->dateTime('actual_return_datetime')->nullable();

            // Duration
            $table->integer('duration_hours');

            // Pricing
            $table->decimal('price_per_day', 10, 2);
            $table->integer('total_days');
            $table->decimal('total_price', 10, 2);

            // Status
            $table->enum('status', ['pending', 'active', 'returned', 'cancelled'])->default('pending');

            // Location & notes
            $table->string('pickup_location')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};