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
        Schema::table('content_progress', function (Blueprint $table) {
            $table->text('feedback')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_progress', function (Blueprint $table) {
            $table->dropColumn('feedback');
        });
    }
};