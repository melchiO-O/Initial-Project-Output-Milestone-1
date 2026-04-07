<?php
// database/migrations/2024_01_01_000001_create_rentals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime');
            $table->integer('duration_hours');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'overdue'])->default('pending');
            $table->string('pickup_location')->nullable();
            $table->string('return_location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
};