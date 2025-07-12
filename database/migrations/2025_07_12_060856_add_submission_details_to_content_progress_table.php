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
            $table->json('submission_details')->nullable()->after('last_attempt_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_progress', function (Blueprint $table) {
            $table->dropColumn('submission_details');
        });
    }
};