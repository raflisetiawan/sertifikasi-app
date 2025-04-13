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
        Schema::create('module_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('content_type'); // video, text, quiz, assignment, file
            $table->unsignedBigInteger('content_id');
            $table->integer('order');
            $table->boolean('is_required')->default(true);
            $table->integer('minimum_duration_seconds')->nullable();
            $table->json('completion_rules')->nullable();
            $table->timestamps();

            $table->index(['module_id', 'order']);
            $table->index(['content_type', 'content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_contents');
    }
};
