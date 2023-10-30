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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Kolom nama
            $table->text('description'); // Kolom deskripsi
            $table->text('facility'); // Kolom fasilitas
            $table->decimal('price', 10, 2); // Kolom harga (contoh: 10.00)
            $table->string('place'); // Kolom tempat
            $table->string('time'); // Kolom waktu (contoh: 1 jam)
            $table->string('image')->nullable(); // Kolom gambar (opsional)
            $table->dateTime('operational'); // Kolom tanggal operasional (contoh: 2023-02-01)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
