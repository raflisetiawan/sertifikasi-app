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
            $table->string('payment_proof')->nullable();
            $table->boolean('verification')->default(false);
            $table->string('payment_status')->default('pending'); // Kolom untuk status pembayaran
            $table->string('midtrans_order_id')->nullable(); // Kolom untuk order_id dari Midtrans
            $table->string('transaction_id')->nullable(); // Kolom untuk transaction_id dari Midtrans
            $table->string('payment_type')->nullable(); // Kolom untuk jenis pembayaran
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
