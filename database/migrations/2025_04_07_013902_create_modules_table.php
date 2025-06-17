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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->integer('order');
            $table->enum('type', ['prework', 'module', 'final']);
            $table->integer('estimated_time_min');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description');
            $table->string('thumbnail')->nullable();
            $table->boolean('is_access_restricted')->default(false);
            $table->timestamp('access_start_at')->nullable();
            $table->timestamp('access_end_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
