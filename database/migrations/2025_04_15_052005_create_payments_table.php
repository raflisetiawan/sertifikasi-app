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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->string('midtrans_order_id')->unique();
            $table->string('transaction_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->string('transaction_status')->default('pending');
            $table->string('fraud_status')->nullable();
            $table->string('snap_token')->nullable();
            $table->string('payment_url')->nullable();
            $table->json('payment_details')->nullable();
            $table->timestamps();

            $table->index(['midtrans_order_id', 'transaction_status']);
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
