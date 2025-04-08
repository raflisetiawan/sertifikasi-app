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
        Schema::create('registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->constrained()->onDelete('cascade')->index();
            $table->unsignedBigInteger('course_id')->constrained()->onDelete('cascade')->index();
            $table->boolean('verification')->default(false);
            $table->string('payment_status')->default('pending');
            $table->string('midtrans_order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('transaction_time')->nullable();
            $table->decimal('gross_amount', 15, 2)->nullable();
            $table->string('fraud_status')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
