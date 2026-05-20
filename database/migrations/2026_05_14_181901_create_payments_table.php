<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migration for payments table
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('rental_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained();
        
        // Payment amounts
        $table->decimal('down_payment', 10, 2)->default(0);
        $table->decimal('remaining_balance', 10, 2)->default(0);
        $table->decimal('damage_fee', 10, 2)->default(0);
        $table->text('damage_notes')->nullable();
        $table->decimal('overdue_fee', 10, 2)->default(0);
        $table->decimal('final_amount_paid', 10, 2)->nullable();
        
        // Payment status & tracking
        $table->enum('payment_status', ['pending', 'partial', 'completed', 'refunded'])->default('pending');
        $table->timestamp('down_payment_paid_at')->nullable();
        $table->timestamp('full_payment_paid_at')->nullable();
        
        // Payment method (optional)
        $table->string('payment_method')->nullable(); 
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
